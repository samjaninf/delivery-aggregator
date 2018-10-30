<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Store;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        if (!auth()->user()->is_admin)
            abort(401);

        return User::query()->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        $params = $request->json()->all();
        $params['password'] = bcrypt($params['password']);
        
        $user = User::create($params);

        foreach($params['permissions'] as $storeCode) {
            $store = Store::findbyCode($storeCode);
            $user->stores()->attach($store);
        }

        return $user;
    }

    public function show($id)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        $user = User::find($id);
        $user->permissions = $user->stores->pluck('code');
        return $user;
    }

    public function update(Request $request)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        $params = $request->json()->all();
        
        if (isset($params['password']))
            $params['password'] = bcrypt($params['password']);
        else
            unset($params['password']);
        
        $user = User::find($params['id']);
        $user->fill($params);

        if (auth()->user()->id === $user->id)
            $user->is_admin = true; // prevents removing own admin flag
        else
            // when is_admin is not defined assume it's false
            $user->is_admin = $request->is_admin ?? false;

        // Update permissions      
        $wanted = collect($params['permissions']);
        $current = $user->stores->pluck('code');

        $to_add = $wanted->except($current);
        foreach($to_add as $storeCode) {
            $store = Store::findbyCode($storeCode);

            $user->stores()->attach($store);
            $user->fb_subscribe_to_group($store);
        }
        
        $to_remove = $current->except($wanted);
        foreach($to_remove as $storeCode) {
            $store = Store::findbyCode($storeCode);
            
            $user->stores()->detach($store);
            $user->fb_unsubscribe_from_group($store);
        }

        $user->save();
        
        return $user;
    }

    public function destroy($id)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        User::find($id)->delete();
    }
}
