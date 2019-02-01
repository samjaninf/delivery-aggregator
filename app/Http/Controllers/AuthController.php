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

    /**
     * Attempt to login and get an auth JWT
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            // If credentials are invalid return error
            return response([
                'status' => 'error',
                'error' => 'invalid.credentials',
                'msg' => 'Invalid Credentials.',
            ], 400);
        }

        $user = auth()->user();
        $fb_device_id = $request->fb_device_id;

        // If a new fb_device_id was provided update it
        if ($fb_device_id && $user->fb_device_id != $fb_device_id) {
            $user->fb_device_id = $fb_device_id;
            $user->save();
        }

        // Return a success status and provide the auth token in the Authorization header
        return response([
            'status' => 'success',
        ])->header('Authorization', $token);
    }

    /**
     * Obtain information about the logged in user
     */
    public function user(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->abilities = $user->abilities;
        return response([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    /**
     * Refresh the JWT
     */
    public function refresh()
    {
        return response([
            'status' => 'success',
        ]);
    }

    /**
     * Logout the current user
     */
    public function logout()
    {
        JWTAuth::invalidate();
        return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.',
        ], 200);
    }
}
