<?php

namespace App\Services;

use App\Store;
use Automattic\WooCommerce\Client;

class WooService
{
    public function createClient($storeCode)
    {
        $store = Store::findByCode($storeCode);

        return new Client(
            $store->url,
            $store->consumer_key,
            $store->consumer_secret,
            [
                'wp_api' => true, // Enable the WP REST API integration
                'version' => 'wc/v2', // WooCommerce WP REST API version
            ]
        );
    }

    public function orders($store, $page = 1)
    {
        $wc = $this->createClient($store);
        $orders = $wc->get('orders', [
            'per_page' => 20,
            'page' => $page,
            'parent' => 0,
        ]);

        return collect($orders)
            ->filter(function ($order) {
                return $order->status != 'pending';
            })
            ->values()
            ->map(function ($order) {
                $items = collect($order->line_items)->map(function ($item) {
                    // Group meta by key
                    $meta = collect($item->meta_data)->mapToGroups(function ($m) {
                        return [$m->key => $m->value];
                    });

                    return [
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'total' => $item->total + $item->total_tax,
                        'meta' => $meta,
                    ];
                });

                list($slot_from, $slot_to) = $this->getTimeslots($order);

                return [
                    'number' => $order->number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'first_name' => $order->shipping->first_name,
                    'last_name' => $order->shipping->last_name,
                    'address' => $order->shipping->address_1,
                    'city' => $order->shipping->city,
                    'phone' => $order->billing->phone,
                    'payment_method' => $order->payment_method,
                    'delivery_date' => $slot_from ?? '',
                    'delivery_date_end' => $slot_to ?? '',
                    'items' => $items ?? [],
                    'notes' => $order->customer_note,
                    'shipping' => $order->shipping_total,
                    'coupons' => collect($order->coupon_lines)->map(function ($c) {
                        return [
                            'code' => $c->code,
                            'discount' => ($c->discount ?? 0) + ($c->discount_tax ?? 0),
                        ];
                    }),
                ];
            });
    }

    public function setOutForDelivery($store, $order)
    {
        $wc = $this->createClient($store);

        $wc->put("orders/$order", [
            'status' => 'out-for-delivery',
        ]);
    }

    public function getTimeslots($order)
    {
        $meta = collect($order->meta_data);

        $date = $meta->firstWhere('key', 'ywcdd_order_delivery_date');

        if ($date) {
            // --- Using YITH plugin ---

            $date = $date->value;

            /*
             * YITH bug workaround: the YITH timeslots plugin
             * uses UTC time to decide which day assign to the timeslot.
             * This means that between 0-1AM orders are assigned to
             * the wrong day. We're fixing it using the delivery date field.
             */
            $slot_from = $meta->firstWhere('key', 'ywcdd_order_slot_from')->value;
            $slot_to = $meta->firstWhere('key', 'ywcdd_order_slot_to')->value;

            try {
                $date = \Carbon\Carbon::parse($date);
                $slot_from = \Carbon\Carbon::createFromTimestamp($slot_from)
                    ->setDateFrom($date)->timestamp;
                $slot_to = \Carbon\Carbon::createFromTimestamp($slot_to)
                    ->setDateFrom($date)->timestamp;
            } catch (\ErrorException $e) {
                $slot_from = $date->timestamp;
                $slot_to = $date->timestamp;
            }
        } else if ($date = $meta->firstWhere('key', 'jckwds_date_ymd')) {
            // --- Using Iconic plugin ---

            $date = $date->value;
            $timeslot = $meta->firstWhere('key', 'jckwds_timeslot');

            if (isset($timeslot->value)) {
                list($slot_from, $slot_to) = explode(' - ', $timeslot->value);
            } else {
                // Requires further investigation
                $slot_from = '00:00 AM';
                $slot_to = '00:00 AM';
            }

            try {
                $slot_from = \Carbon\Carbon::createFromFormat('Ymd H:i+', "$date $slot_from")->timestamp;
                $slot_to = \Carbon\Carbon::createFromFormat('Ymd H:i+', "$date $slot_to")->timestamp;
            } catch (\ErrorException $e) {
                $slot_from = $date->timestamp;
                $slot_to = $date->timestamp;
            }
        } else {
            // --- Missing timeslot info ---
            $slot_from = null;
            $slot_to = null;
        }

        return [$slot_from, $slot_to];
    }

    public function products($store, $page = 1)
    {
        $wc = $this->createClient($store);
        $products = $wc->get('products', [
            'per_page' => 100,
        ]);

        return collect($products)
            ->map(function ($product) {
                $category = collect($product->categories)->first();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $category->name ?? 'Senza categoria',
                    'in_stock' => $product->in_stock,
                ];
            });
    }
}
