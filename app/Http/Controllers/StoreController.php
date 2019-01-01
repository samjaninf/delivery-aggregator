<?php

namespace App\Http\Controllers;

use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        if (Gate::allows('view all stores')) {
            return Store::all();
        } else {
            return auth()->user()->stores()->get(['name', 'code']);
        }

    }

    public function store(Request $request)
    {
        if (Gate::denies('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::create($params);
    }

    public function show($storeCode)
    {
        if (Gate::denies('manage stores')) {
            abort(401);
        }

        return Store::findByCode($storeCode);
    }

    public function update(Request $request)
    {
        if (Gate::denies('manage stores')) {
            abort(401);
        }

        $params = $request->json()->all();
        $store = Store::find($params['id']);
        $store->fill($params);
        $store->save();
    }

    public function destroy($storeCode)
    {
        if (Gate::denies('manage stores')) {
            abort(401);
        }

        Store::findByCode($storeCode)->delete();
    }
}
