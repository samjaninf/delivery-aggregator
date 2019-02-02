<?php

namespace App\Http\Controllers;

use App\Services\WooService;
use App\StatusChange;
use App\Store;
use Bouncer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WooController extends Controller
{
    public function __construct(WooService $woo)
    {
        // Inject a WooService instance to use in this controller
        $this->woo = $woo;
        $this->middleware('auth:api');
    }

    /**
     * A list of the orders for the specified store (and page)
     */
    public function orders(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('view orders', $s)) {
            abort(401);
        }
        try {
            return $this->woo->orders($store, $request->page);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Update an order status to "Out for delivery"
     */
    public function orderOutForDelivery(Request $request, $store, $order)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('set out for delivery', $s)) {
            abort(401);
        }

        try {
            $this->woo->setOutForDelivery($store, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }

        // Log order status change
        $s = new StatusChange;
        $s->order = $order;
        $s->status = 'out-for-delivery';
        $s->user()->associate(auth()->user());
        $s->store()->associate(Store::findByCode($store));
        $s->save();
    }

    /**
     * Update an order status to "Completed"
     */
    public function orderCompleted(Request $request, $store, $order)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('set completed', $s)) {
            abort(401);
        }

        try {
            $this->woo->setCompleted($store, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }

        // Log order status change
        $s = new StatusChange;
        $s->order = $order;
        $s->status = 'completed';
        $s->user()->associate(auth()->user());
        $s->store()->associate(Store::findByCode($store));
        $s->save();
    }

    /**
     * Update an order to set "Seen" as true
     */
    public function orderSeen(Request $request, $store, $order)
    {
        $s = Store::findByCode($store);
        // Only managers can do this
        if (!isset($s) || Bouncer::cannot('set seen', $s)) {
            abort(401);
        }

        try {
            $this->woo->setSeen($store, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Update an order to set "Prepared" as true
     */
    public function orderPrepared(Request $request, $store, $order)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('set prepared', $s)) {
            abort(401);
        }

        try {
            $this->woo->setPrepared($store, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Return a list of all products from a store
     */
    public function products(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage products', $s)) {
            abort(401);
        }

        try {
            return $this->woo->products($store);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }

    }

    /**
     * Update a product stock status
     */
    public function updateProduct(Request $request, $store, $product)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage products', $s)) {
            abort(401);
        }

        $in_stock = $request->in_stock;

        try {
            $product = $this->woo->updateInStock($store, $product, $in_stock);
            return response()->json($product);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }
}
