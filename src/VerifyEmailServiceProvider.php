<?php

namespace ImranAli\VerifyEmail;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class VerifyEmailServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        $this->app->singleton(verifyEmail::class, function($app) {
            return new verifyEmail($app);
        });
    }

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot() {
        $this->publishConfig();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [verifyEmail::class];
    }

    /**
     * Publish package configuration
     * @return void
     */
    private function publishConfig()
    {
        $this->publishes([
           __DIR__.'/../config/verifyemail.php' => 'config/verifyemail.php'
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/verifyemail.php','verifyemail');
    }

}