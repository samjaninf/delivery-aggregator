<?php
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Cake\Database\Connection;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$db = new Connection([
	'driver' => 'Cake\Database\Driver\Mysql',
    'database' => 'delivery',
    'username' => 'root',
    'password' => 'password'
]);

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

function create_client($store_name) {
    global $db;

    $stmt = $db->execute("SELECT url, consumer_key, consumer_secret FROM stores where code = :code", ['code' => $store_name]);
    $store = $stmt->fetch('assoc');

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

    return $wc;
}

// Fetch action
switch ($_GET['path']) {
    case 'orders':
        listOrders();
        break;
    case 'stores':
        listStores();
        break;
    default:
        exit('Invalid path');
}

//
function listOrders()
{
    $wc = create_client($_GET['store']);

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
            'delivery_date_end' => array_find($order->meta_data, 'ywcdd_order_slot_to'),
            'items' => $items ?? [],
        ];
    }, $orders);

    // echo json_encode($orders);
    echo json_encode($res);
}

function listStores() {
    global $db;

    $stmt = $db->execute('SELECT name, code FROM stores');
    $stores = $stmt->fetchAll('assoc');

    echo json_encode($stores);
}
?>
