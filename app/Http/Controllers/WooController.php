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

            /*
             * YITH bug workaround: the YITH timeslots plugin
             * uses UTC time to decide which day assign to the timeslot.
             * This means that between 0-1AM orders are assigned to
             * the wrong day. We're fixing it using the delivery date field.
             */
            $date = collect($order->meta_data)->firstWhere(
                'key',
                'ywcdd_order_delivery_date'
            )->value;

            $slot_from = collect($order->meta_data)->firstWhere(
                'key',
                'ywcdd_order_slot_from'
            )->value;

            $slot_to = collect($order->meta_data)->firstWhere(
                'key',
                'ywcdd_order_slot_to'
            )->value;

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
                'order' => $order,
            ];
        });
    }
}
