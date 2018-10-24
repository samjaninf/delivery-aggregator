<?php
require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Helper functions
function array_find($xs, $k)
{
    foreach ($xs as $x) {
        if ($x->key === $k) {
            return $x->value;
        }
    }
    return null;
}

// Setup Stores
$stores = [
    'imburger' => [
        'url' => 'https://imburger.it',
        'consumer_key' => '***REMOVED***',
        'consumer_secret' => '***REMOVED***'
    ],
    'dascomposto' => [
        'url' => 'http://delivery.dascomposto.it',
        'consumer_key' => '***REMOVED***',
        'consumer_secret' => '***REMOVED***'
    ]
];

// Create client
$store = $stores[$_GET['store']];
if (!$store) {
    exit('Invalid store parameter');
}

$wc = new Client(
    $store['url'],
    $store['consumer_key'],
    $store['consumer_secret'],
    [
        'wp_api' => true, // Enable the WP REST API integration
        'version' => 'wc/v2' // WooCommerce WP REST API version
    ]
);

// Fetch action
switch ($_GET['path']) {
    case 'orders':
        listOrders($wc);
        break;
    default:
        exit('Invalid path');
}

//
function listOrders($wc)
{
    $page = $_GET['page'] ?? 1;
    $orders = $wc->get('orders', ['per_page' => 12, 'page' => $page, 'parent' => 0]);
    $res = array_map(function ($order) {
        $items = array_map(function($item) {
            
            // Group meta by key
            $meta = [];
            foreach($item->meta_data as $m) {
                if(array_key_exists('key', $m)){
                    $meta[$m->key] = $m->value;
                }
            }

            return [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'total' => $item->total + $item->total_tax,
                'meta' => $meta,
            ];
        }, $order->line_items);

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
            'delivery_date' => array_find($order->meta_data, 'ywcdd_order_slot_from'),
            'items' => $items ?? [],
        ];
    }, $orders);

    // echo json_encode($orders);
    echo json_encode($res);
}
?>
