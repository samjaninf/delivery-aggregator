<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Automattic\WooCommerce\Client;
use App\Store;

class WooController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function createClient($storeCode)
    {
        $store = Store::findByCode($storeCode);

        return new Client(
            $store->url,
            $store->consumer_key,
            $store->consumer_secret,
            [
                'wp_api' => true, // Enable the WP REST API integration
                'version' => 'wc/v2' // WooCommerce WP REST API version
            ]
        );
    }

    function orders(Request $request, $store)
    {
        if(!auth()->user()->is_admin &&
           !auth()->user()->stores()->where('code', $store)->first())
            abort(401);
        
        $wc = $this->createClient($store);
        $page = $request->page ?? 1;

        $orders = $wc->get('orders', [
            'per_page' => 20,
            'page' => $page,
            'parent' => 0
        ]);

        return collect($orders)
            ->filter(function ($order) {
                return $order->status != 'pending';
            })
            ->values()
            ->map(function ($order) {
            $items = collect($order->line_items)->map(function ($item) {
                // Group meta by key
                $meta = collect($item->meta_data)->mapToGroups(function($m) {
                    return [ $m->key => $m->value ];
                });

                return [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'total' => $item->total + $item->total_tax,
                    'meta' => $meta
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
                'coupons' => collect($order->coupon_lines)->map(function($c) {
                    return [
                        'code' => $c->code,
                        'discount' => ($c->discount ?? 0) + ($c->discount_tax ?? 0),
                    ];
                }),
            ];
        });
    }

    function orderOutForDelivery(Request $request, $store, $order)
    {
        if(!auth()->user()->is_admin &&
           !auth()->user()->stores()->where('code', $store)->first())
            abort(401);
        
        $wc = $this->createClient($store);

        try {
            $wc->put("orders/$order", [
                'status' => 'out-for-delivery'
            ]);
        } catch(\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            return abort(422, $e->getMessage());
        }
    }

    function getTimeslots($order)
    {
        $meta = collect($order->meta_data);

        $date = $meta->firstWhere('key','ywcdd_order_delivery_date');

        if ($date) {
            // --- Using YITH plugin ---

            $date = $date->value;

            /*
             * YITH bug workaround: the YITH timeslots plugin
             * uses UTC time to decide which day assign to the timeslot.
             * This means that between 0-1AM orders are assigned to
             * the wrong day. We're fixing it using the delivery date field.
             */   
            $slot_from = $meta->firstWhere('key','ywcdd_order_slot_from')->value;
            $slot_to = $meta->firstWhere('key','ywcdd_order_slot_to')->value;
    
            try {
                $date = \Carbon\Carbon::parse($date);
                $slot_from = \Carbon\Carbon::createFromTimestamp($slot_from)
                    ->setDateFrom($date)->timestamp;
                $slot_to = \Carbon\Carbon::createFromTimestamp($slot_to)
                    ->setDateFrom($date)->timestamp;
            } catch(\ErrorException $e) {
                $slot_from = $date->timestamp;
                $slot_to = $date->timestamp;
            }
        } else if ($date = $meta->firstWhere('key', 'jckwds_date_ymd')) {
            // --- Using Iconic plugin ---

            $date = $date->value;
            $timeslot = $meta->firstWhere('key', 'jckwds_timeslot')->value;
            list($slot_from, $slot_to) = explode(' - ', $timeslot);
            
            try {
                $slot_from = \Carbon\Carbon::createFromFormat('Ymd H:i+', "$date $slot_from")->timestamp;
                $slot_to = \Carbon\Carbon::createFromFormat('Ymd H:i+', "$date $slot_to")->timestamp;
            } catch(\ErrorException $e) {
                $slot_from = $date->timestamp;
                $slot_to = $date->timestamp;
            }
        }
        else {
            // --- Missing timeslot info ---
            $slot_from = null;
            $slot_to = null;
        }

        return [$slot_from, $slot_to];
    }
}
