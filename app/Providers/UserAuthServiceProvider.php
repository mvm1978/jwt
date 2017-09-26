<?php

namespace App\Providers;

use App\Auth\UserAuthProvider;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class UserAuthServiceProvider extends ServiceProvider
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
        $this->app['auth']->provider('userAuth', function() {
            return new UserAuthProvider();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
