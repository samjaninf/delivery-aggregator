<?php

namespace App\Http\Controllers;

use App\Store;
use App\User;
use Bouncer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List all the users registered on the server
     */
    public function index()
    {
        if (Bouncer::cannot('manage users')) {
            abort(401);
        }

        return User::query()->get(['id', 'name']);
    }

    /**
     * Create a new user with the provided parameters
     */
    public function store(Request $request)
    {
        if (Bouncer::cannot('manage users')) {
            abort(401);
        }

        $params = $request->json()->all();
        // Hash the password using bcrypt
        $params['password'] = bcrypt($params['password']);

        $user = User::create($params);

        // Give permission to the user to access the provided stores
        foreach ($params['permissions'] as $storeCode) {
            $store = Store::findbyCode($storeCode);
            $user->stores()->attach($store);
        }

        // Assign the correct role to the user
        $roleName = $params['role'];
        $role = Bouncer::role()->where('name', $roleName)->first();
        if (!$role) {
            // Invalid role provided
            abort(422);
        }
        Bouncer::sync($user)->roles([$role]);

        return $user;
    }

    /**
     * Show a specific user data
     */
    public function show($id)
    {
        if (Bouncer::cannot('manage users')) {
            abort(401);
        }

        $user = User::find($id);
        $user->permissions = $user->stores->pluck('code');
        $user->role = $user->getRoles()->first();
        return $user;
    }

    /**
     * Update the store with the provided parameters
     */
    public function update(Request $request)
    {
        if (Bouncer::cannot('manage users')) {
            abort(401);
        }

        $params = $request->json()->all();

        if (isset($params['password'])) {
            // If a new password is specified hash it with bcrypt
            $params['password'] = bcrypt($params['password']);
        } else {
            // Else make sure that the password is not updated
            unset($params['password']);
        }

        // Prevent user from changing their own role
        if (auth()->user()->id === $params['id']) {
            unset($params['role']);
        } else if (!isset($params['role'])) {
            abort(422);
        }

        $user = User::find($params['id']);
        $user->fill($params);

        // Assign the correct role to the user
        $roleName = $params['role'];
        $role = Bouncer::role()->where('name', $roleName)->first();
        if (!$role) {
            // Invalid role provided
            abort(422);
        }
        Bouncer::sync($user)->roles([$role]);

        // Update permissions
        $wanted = collect($params['permissions']);
        $current = $user->stores->pluck('code');

        // Wanted - Current => the stores permissions we need to add
        $to_add = $wanted->diff($current);
        foreach ($to_add as $storeCode) {
            $store = Store::findbyCode($storeCode);

            // Create a relationship between the user and the store
            $user->stores()->attach($store);
            // Subscribe the user to the Firebase group
            $user->fb_subscribe_to_group($store);
        }

        // Current - Wanted => the stores permissions we need to remove
        $to_remove = $current->diff($wanted);
        foreach ($to_remove as $storeCode) {
            $store = Store::findbyCode($storeCode);

            // Remove the relationship between the user and the store
            $user->stores()->detach($store);
            // Unsubscribe the user from the Firebase group
            $user->fb_unsubscribe_from_group($store);
        }

        $user->save();

        return $user;
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        // FIXME: not working - need to delete all of the StatusChanges (or soft-delete the user)

        if (Bouncer::cannot('manage users')) {
            abort(401);
        }

        User::find($id)->delete();
    }
}
