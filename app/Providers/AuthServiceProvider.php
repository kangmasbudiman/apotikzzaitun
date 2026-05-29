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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isAdmin', function ($user)
        {
            return $user->hakAkses == '1';
        });

        Gate::define('isKasir', function ($user)
        {
            return $user->hakAkses == '2';
        });

        Gate::define('isGudang', function ($user)
        {
            return $user->hakAkses == '3';
        });

        Gate::define('isAdminKasir', function ($user)
        {
            return $user->hakAkses == '1' || $user->hakAkses == '2';
        });

        Gate::define('isAdminGudang', function ($user)
        {
            return $user->hakAkses == '1' || $user->hakAkses == '3';
        });
    }
}
