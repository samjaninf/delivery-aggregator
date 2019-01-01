<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response([
                'status' => 'error',
                'error' => 'invalid.credentials',
                'msg' => 'Invalid Credentials.',
            ], 400);
        }

        $user = auth()->user();
        $fb_device_id = $request->fb_device_id;

        // If a fb_device_id was provided update it
        if ($fb_device_id && $user->fb_device_id != $fb_device_id) {
            $user->fb_device_id = $fb_device_id;
            $user->save();
        }

        return response([
            'status' => 'success',
        ])
            ->header('Authorization', $token);
    }

    public function user(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->roles = $user->roles;
        return response([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function refresh()
    {
        return response([
            'status' => 'success',
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate();
        return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.',
        ], 200);
    }
}
