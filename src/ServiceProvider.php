<?php

namespace Ttrig\Billmate;

use Ttrig\Billmate\Middlewares\TransformRedirectRequest;
use Ttrig\Billmate\Middlewares\VerifyRequest;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/billmate.php', 'billmate');
    }

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/billmate.php' => config_path('billmate.php')], 'config');

        $this->loadViewsFrom(__DIR__ . '/views', 'billmate');

        $this->registerRoutes();
    }

    public function registerRoutes()
    {
        $routeConfig = [
            'prefix' => config('billmate.route_prefix'),
            'as' => 'billmate.',
        ];

        $this->app['router']->group($routeConfig, function ($router) {
            $router->post('accept', config('billmate.accept_action'))
                ->name('accept')
                ->middleware([TransformRedirectRequest::class, VerifyRequest::class]);

            $router->post('cancel', config('billmate.cancel_action'))
                ->name('cancel')
                ->middleware([TransformRedirectRequest::class, VerifyRequest::class]);

            $router->post('callback', config('billmate.callback_action'))
                ->name('callback')
                ->middleware(VerifyRequest::class);
        });
    }
}
