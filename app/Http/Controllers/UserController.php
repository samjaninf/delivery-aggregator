<?php

namespace App\Http\Controllers;

use App\Store;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        if (Gate::denies('manage users')) {
            abort(401);
        }

        return User::query()->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        if (Gate::denies('manage users')) {
            abort(401);
        }

        $params = $request->json()->all();
        $params['password'] = bcrypt($params['password']);

        $user = User::create($params);

        foreach ($params['permissions'] as $storeCode) {
            $store = Store::findbyCode($storeCode);
            $user->stores()->attach($store);
        }

        return $user;
    }

    public function show($id)
    {
        if (Gate::denies('manage users')) {
            abort(401);
        }

        $user = User::find($id);
        $user->permissions = $user->stores->pluck('code');
        return $user;
    }

    public function update(Request $request)
    {
        if (Gate::denies('manage users')) {
            abort(401);
        }

        $params = $request->json()->all();

        if (isset($params['password'])) {
            $params['password'] = bcrypt($params['password']);
        } else {
            unset($params['password']);
        }

        // prevents removing own admin flag
        if (auth()->user()->id === $params['id']) {
            unset($params['is_admin']);
        }

        $user = User::find($params['id']);
        $user->fill($params);

        // Update permissions
        $wanted = collect($params['permissions']);
        $current = $user->stores->pluck('code');

        $to_add = $wanted->diff($current);
        foreach ($to_add as $storeCode) {
            $store = Store::findbyCode($storeCode);

            $user->stores()->attach($store);
            $user->fb_subscribe_to_group($store);
        }

        $to_remove = $current->diff($wanted);
        foreach ($to_remove as $storeCode) {
            $store = Store::findbyCode($storeCode);

            $user->stores()->detach($store);
            $user->fb_unsubscribe_from_group($store);
        }

        $user->save();

        return $user;
    }

    public function destroy($id)
    {
        if (Gate::denies('manage users')) {
            abort(401);
        }

        User::find($id)->delete();
    }
}
