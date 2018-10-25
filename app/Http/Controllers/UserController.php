<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserController extends Controller
{
    public function index()
    {
        return User::query()->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        $params = $request->json()->all();
        $params['password'] = bcrypt($params['password']);
        
        return User::create($params);
    }

    public function show($id)
    {
        return User::find($id);
    }

    public function update(Request $request)
    {
        $params = $request->json()->all();
        if (isset($params['password']))
            $params['password'] = bcrypt($params['password']);
        
        $user = User::find($params['id']);
        $user->fill($params);
        $user->save();

        return $user;
    }

    public function destroy($id)
    {
        User::find($id)->delete();
    }
}
