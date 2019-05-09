<?php

namespace App\Services;

use App\Store;
use Automattic\WooCommerce\Client;

/**
 * WooService is responsible for the interaction between the server and the WooCommerce stores
 */
class WooService
{
    /**
     * Create a client to interact with the API of a specific store
     */
    public function createClient($store)
    {
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

    /**********************
     *  ORDERS FUNCTIONS  *
     **********************/

    /**
     * Fetch a paginated list of the orders belonging to a specific store
     *
     * @param string $store The desidered store
     * @param int    $page  The page we want to obtain
     *
     * @return array A list of the desidered orders
     */
    public function orders($store, $page = 1, $vendor = null)
    {
        $wc = $this->createClient($store);
        $args = [
            'per_page' => 20,
            'page' => $page,
        ];
        if ($vendor) {
            $args['excluding_parent'] = 0;
            $args['vendor'] = $vendor->code;

            $fromSuperstore = $store->code;
        } else {
            $args['parent'] = 0;

            $fromSuperstore = false;
        }

        $orders = $wc->get('orders', $args);

        return collect($orders)
            ->filter(function ($order) {
                // Filter out orders with pending status
                return $order->status != 'pending';
            })
            ->values()
            ->map(function ($order) use ($fromSuperstore) {
                // Map each order to the format that the frontend expects

                // Order items list
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

                // Get correct order timeslot
                list($slot_from, $slot_to) = $this->getTimeslots($order);

                $meta = collect($order->meta_data);
                $prepared = $meta->firstWhere('key', 'prepared');
                $seen = $meta->firstWhere('key', 'seen');
                $pickupLocation = $meta->firstWhere('key', '_billing_place');
                $pickupTime = $meta->firstWhere('key', 'da_time');

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
                    'prepared' => !!($prepared ?? false),
                    'seen' => !!($seen ?? false),
                    'from_superstore' => $fromSuperstore,
                    'pickup_location' => $pickupLocation->value ?? null,
                    'pickup_time' => $pickupTime->value ?? null,
                ];
            });
    }

    /**
     * Fetch data about orders with the specified IDs
     *
     * @param string $store The desidered store
     * @param array  $ids   The order IDs we're interested in
     *
     * @return array A list of orders about the requested orders
     */
    public function ordersWithId($store, $ids)
    {
        $wc = $this->createClient($store);
        return collect(
            $wc->get('orders', [
                'include' => $ids,
                'per_page' => 100,
            ])
        )->map(function ($order) {
            list($slot_from, $slot_to) = $this->getTimeslots($order);

            return [
                'number' => $order->number,
                'first_name' => $order->shipping->first_name,
                'last_name' => $order->shipping->last_name,
                'delivery_date' => $slot_from ?? '',
                'delivery_date_end' => $slot_to ?? '',
            ];
        });
    }

    /**
     * Update order status to "Out for delivery"
     *
     * @param string $store The desidered store
     * @param string $order The ID of the order we want to update
     */
    public function setOutForDelivery($store, $order)
    {
        $wc = $this->createClient($store);

        $wc->put("orders/$order", [
            'status' => 'out-for-delivery',
        ]);
    }

    /**
     * Update order status to "Completed"
     *
     * @param string $store The desidered store
     * @param string $order The ID of the order we want to update
     */
    public function setCompleted($store, $order)
    {
        $wc = $this->createClient($store);

        $wc->put("orders/$order", [
            'status' => 'completed',
        ]);
    }

    /**
     * Update order meta "Prepared" value to true
     *
     * @param string $store The desidered store
     * @param string $order The ID of the order we want to update
     */
    public function setPrepared($store, $order)
    {
        $wc = $this->createClient($store);

        return $wc->put("orders/$order", [
            'meta_data' => [
                [
                    'key' => 'prepared',
                    'value' => true,
                ],
            ],
        ]);
    }

    /**
     * Update order meta "Seen" value to true
     *
     * @param string $store The desidered store
     * @param string $order The ID of the order we want to update
     */
    public function setSeen($store, $order)
    {
        $wc = $this->createClient($store);

        return $wc->put("orders/$order", [
            'meta_data' => [
                [
                    'key' => 'seen',
                    'value' => true,
                ],
            ],
        ]);
    }

    /**
     * Parse timeslot data from an order meta
     * Supports both YITH and Iconic timeslot plugins
     *
     * @param \WooCommerce\Order $order The order we want to get the timeslot from
     *
     * @return array An array with [$timeslot_start, $timeslot_end] in a UNIX timestamp
     */
    public function getTimeslots($order)
    {
        $meta = collect($order->meta_data);

        $date = $meta->firstWhere('key', 'ywcdd_order_delivery_date');

        if ($date) {
            // --- Using YITH timeslot plugin ---

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
            // --- Using Iconic timeslot plugin ---

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

    /***********************
     *  PRODUCT FUNCTIONS  *
     ***********************/

    /**
     * Fetch a list of all the products from the specified store
     *
     * @param string $store The desidered store
     *
     * @return array List of all the products of the specified store
     */
    public function products($store)
    {
        $wc = $this->createClient($store);
        $products = $wc->get('products', [
            'per_page' => 100, // FIXME: needs to be fixed for > 100 products
        ]);

        return collect($products)
            ->map(function ($product) {
                // Map each product to the format that the frontend expects
                $category = collect($product->categories)->first();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $category->name ?? 'Senza categoria',
                    'in_stock' => $product->in_stock,
                ];
            });
    }

    /**
     * Update product stock status
     *
     * @param string $store    The desidered store
     * @param string $product  The ID of the product we want to update
     * @param bool   $in_stock The new "In stock" value
     */
    public function updateInStock($store, $product, $in_stock)
    {
        $wc = $this->createClient($store);

        return $wc->put("products/$product", [
            'in_stock' => $in_stock,
        ]);
    }

    public function deliverySlotsSettings($store)
    {
        $url = "{$store->url}/wp-json/delivery-slots/v1/settings";

        $headers = [
            "CS: {$store->consumer_secret}",
            "CK: {$store->consumer_key}",
        ];

        return curl_get($url, $headers);
    }

    public function setDeliverySlotsSettings($store, $lockout, $cutoff)
    {
        $url = "{$store->url}/wp-json/delivery-slots/v1/settings";

        $headers = [
            "CS: {$store->consumer_secret}",
            "CK: {$store->consumer_key}",
        ];

        $body = [
            'lockout' => $lockout,
            'cutoff' => $cutoff,
        ];

        return curl_post($url, $headers, $body);
    }

    public function isOpen($store)
    {
        $url = "{$store->url}/wp-json/vacation-state/v1/settings";

        $headers = [
            "CS: {$store->consumer_secret}",
            "CK: {$store->consumer_key}",
        ];

        $response = curl_get($url, $headers);

        return !!($response->isopen ?? false);
    }

    public function setIsOpen($store, $isOpen)
    {
        $url = "{$store->url}/wp-json/vacation-state/v1/settings";

        $headers = [
            "CS: {$store->consumer_secret}",
            "CK: {$store->consumer_key}",
        ];

        $body = [
            'isopen' => $isOpen,
        ];

        return curl_post($url, $headers, $body);
    }
}
