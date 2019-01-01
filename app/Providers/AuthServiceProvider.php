<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        Gate::define('view orders', function ($user, $store) {
            return $user->hasPermissionForStore($store);
        });
        Gate::define('set delivered', function ($user, $store) {
            return $user->hasPermissionForStore($store);
        });
        Gate::define('manage products', function ($user, $store) {
            return $user->hasRole('manager')
            && $user->hasPermissionForStore($store);
        });

        Gate::define('view all stores', function ($user) {
            return false;
        });
        Gate::define('view statuslog', function ($user) {
            return false;
        });
        Gate::define('manage stores', function ($user) {
            return false;
        });
        Gate::define('manage users', function ($user) {
            return false;
        });
    }
}
