<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Store;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        if (!auth()->user()->is_admin)
            abort(401);
        
        if (auth()->user()->is_admin)
            return Store::all();
        else
            return auth()->user()->stores()->get(['name', 'code']);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->is_admin)
            abort(401);
        
        $params = $request->json()->all();
        $store = Store::create($params);
    }

    public function show($storeCode)
    {
        if (!auth()->user()->is_admin)
            abort(401);
        
        return Store::findByCode($storeCode);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->is_admin)
            abort(401);
        
        $params = $request->json()->all();
        $store = Store::find($params['id']);
        $store->fill($params);
        $store->save();
    }

    public function destroy($storeCode)
    {
        if (!auth()->user()->is_admin)
            abort(401);
            
        Store::findByCode($storeCode)->delete();
    }
}
