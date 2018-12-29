<?php

namespace App\Http\Controllers;

use App\Services\WooService;
use App\StatusChange;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WooController extends Controller
{
    public function __construct(WooService $woo)
    {
        $this->woo = $woo;
        $this->middleware('auth:api');
    }

    public function orders(Request $request, $store)
    {
        if (!auth()->user()->is_admin &&
            !auth()->user()->stores()->where('code', $store)->first()) {
            abort(401);
        }

        return $this->woo->orders($store, $request->page);
    }

    public function orderOutForDelivery(Request $request, $store, $order)
    {
        if (!auth()->user()->is_admin &&
            !auth()->user()->stores()->where('code', $store)->first()) {
            abort(401);
        }

        try {
            $this->woo->setOutForDelivery($store, $order);
        } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {
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

    public function products(Request $request, $store)
    {
        if (!auth()->user()->is_admin &&
            !auth()->user()->stores()->where('code', $store)->first()) {
            abort(401);
        }

        return $this->woo->products($store);
    }
}
