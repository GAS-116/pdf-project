<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

        // Horizon::night();
    }

    protected function authorization()
    {
        Horizon::auth(function ($request) {
            return true;
        });
    }

//    /**
//     * Register the Horizon gate.
//     *
//     * This gate determines who can access Horizon in non-local environments.
//     *
//     * @return void
//     */
//    protected function gate()
//    {
//        Gate::define('viewHorizon', function ($user) {
//            return true;
//        });
////        Gate::define('viewHorizon', function ($user) {
////            return in_array($user->email, [
////                //
////            ]);
////        });
//    }
}
