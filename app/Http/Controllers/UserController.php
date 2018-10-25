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

        $user->stores()->detach();
        foreach($params['permissions'] as $storeCode => $active) {
            $store = Store::findbyCode($storeCode);

            if ($active) {
                $user->stores()->attach($store);
            }
        }

        return $user;
    }

    public function show($id)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        $user = User::find($id);
        $user->permissions =
            $user->stores->mapWithKeys(function($s) {
                return [ $s->code => true ];
            });
        if ($user->permissions->isEmpty())
            $user->permissions = new \stdClass; // force json object
        return $user;
    }

    public function update(Request $request)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        $params = $request->json()->all();
        if (isset($params['password']))
            $params['password'] = bcrypt($params['password']);
        
        $user = User::find($params['id']);
        $user->fill($params);

        if (auth()->user()->id === $user->id)
            $user->is_admin = true; // prevents removing own admin flag

        $user->save();

        $user->stores()->detach();
        foreach($params['permissions'] as $storeCode => $active) {
            $store = Store::findbyCode($storeCode);

            if ($active) {
                $user->stores()->attach($store);
            }
        }

        return $user;
    }

    public function destroy($id)
    {
        if (!auth()->user()->is_admin)
            abort(401);

        User::find($id)->delete();
    }
}
