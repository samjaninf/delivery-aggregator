<?php

namespace App\Providers;

use Bouncer;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define owning a store
        Bouncer::ownedVia(\App\Store::class, function ($store, $user) {
            return $user->hasPermissionForStore($store);
        });

    }
}
