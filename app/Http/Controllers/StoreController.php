<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Store;

class StoreController extends Controller
{
    public function index()
    {
        return Store::query()->get(['name', 'code']);
    }

    public function store(Request $request)
    {
        $params = $request->json()->all();
        $store = Store::create($params);
    }

    public function show($storeCode)
    {
        return Store::findByCode($storeCode);
    }

    public function update(Request $request)
    {
        $params = $request->json()->all();
        $store = Store::find($params['id']);
        $store->fill($params);
        $store->save();
    }

    public function destroy($storeCode)
    {
        Store::findByCode($storeCode)->delete();
    }
}
