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
            // Own orders
            $orders = $this->woo->orders($s, $request->page);

            // Load orders from superstores
            foreach ($s->superstores as $ss) {
                $ssOrders = $this->woo->orders($ss, $request->page, $s);
                $orders = $orders->merge($ssOrders);
            }

            return $orders;

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
            $this->woo->setOutForDelivery($s, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }

        // Log order status change
        $s = new StatusChange;
        $s->order = $order;
        $s->status = 'out-for-delivery';
        $s->user()->associate(auth()->user());
        $s->store()->associate($s);
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
            $this->woo->setCompleted($s, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }

        // Log order status change
        $s = new StatusChange;
        $s->order = $order;
        $s->status = 'completed';
        $s->user()->associate(auth()->user());
        $s->store()->associate($s);
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
            $this->woo->setSeen($s, $order);
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
            $this->woo->setPrepared($s, $order);
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
            return $this->woo->products($s);
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
            $product = $this->woo->updateInStock($s, $product, $in_stock);
            return response()->json($product);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Return the delivery slot settings of a store
     */
    public function deliverySlotsSettings(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage delivery slots', $s)) {
            abort(401);
        }

        try {
            $settings = $this->woo->deliverySlotsSettings($s);
            return response()->json($settings);
        } catch (Exception $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Update the delivery slot settings for a store
     */
    public function setDeliverySlotsSettings(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage delivery slots', $s)) {
            abort(401);
        }

        $lockout = $request->lockout;
        $cutoff = $request->cutoff;

        try {
            $this->woo->setDeliverySlotsSettings($s, $lockout, $cutoff);
        } catch (Exception $e) {
            // Return error message
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Return the isOpen setting of a store
     */
    public function isOpen(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage store opening', $s)) {
            abort(401);
        }

        try {
            $isOpen = $this->woo->isOpen($s);

            return [
                'isOpen' => $isOpen,
            ];

        } catch (Exception $e) {
            return abort(422, $e->getMessage());
        }
    }

    /**
     * Update the isOpen setting for a store
     */
    public function setIsOpen(Request $request, $store)
    {
        $s = Store::findByCode($store);
        if (!isset($s) || Bouncer::cannot('manage store opening', $s)) {
            abort(401);
        }

        $isOpen = $request->isOpen ?? true;

        try {
            $this->woo->setIsOpen($s, $isOpen);
        } catch (Exception $e) {
            return abort(422, $e->getMessage());
        }
    }
}
