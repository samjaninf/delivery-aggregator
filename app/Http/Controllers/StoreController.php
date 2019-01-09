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

    public function index()
    {
        if (Bouncer::can('view all stores')) {
            return Store::all();
        } else {
            return auth()->user()->stores()->get(['name', 'code']);
        }

    }

    public function store(Request $request)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::create($params);
    }

    public function show($storeCode)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        return Store::findByCode($storeCode);
    }

    public function update(Request $request)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::find($params['id']);
        $store->fill($params);
        $store->save();
    }

    public function destroy($storeCode)
    {
        if (Bouncer::cannot('manage stores')) {
            abort(401);
        }

        Store::findByCode($storeCode)->delete();
    }
}
