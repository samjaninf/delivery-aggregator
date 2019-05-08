<?php

namespace App\Http\Controllers;

use App\Store;
use Bouncer;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Return list of stores the user has access to
     */
    public function index()
    {
        if (Bouncer::can('view all stores')) {
            return Store::all();
        } else {
            return auth()->user()->stores()->get(['name', 'code']);
        }
    }

    /**
     * Create a new store with provided parameters
     */
    public function store(Request $request)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::create($params);
    }

    /**
     * Show a specific store data
     */
    public function show($storeCode)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        $store = Store::findByCode($storeCode);
        $store->__substores = $store->substores->pluck('code');
        $store->makeHidden('substores');

        return $store;
    }

    /**
     * Update the store with the provided parameters
     */
    public function update(Request $request)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::find($params['id']);
        $store->fill($params);

        if ($params['is_superstore']) {
            // Update selected substores
            $store->is_superstore = true;
            $substoresIds = collect($params['substores'])
                ->map(function ($code) {
                    $store = Store::findByCode($code);
                    if (!$store) {
                        return null;
                    }
                    return $store->id;
                })
                ->filter(function ($store) {
                    return isset($store);
                });

            $store->substores()->sync($substoresIds);
        } else {
            // Detach all substores
            $store->is_superstore = false;
            $store->substores()->detach();
        }

        $store->save();
    }

    /**
     * Delete a store
     */
    public function destroy($storeCode)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        Store::findByCode($storeCode)->delete();
    }
}
