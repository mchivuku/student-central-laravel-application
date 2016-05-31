<?php

namespace StudentCentralCourseBrowser\Providers;

use Illuminate\Support\ServiceProvider;

/***
 * Class AppServiceProvider
 * @package StudentCentralCourseBrowser\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'DatabaseExtensions',
            'StudentCentralCourseBrowser\Jobs\DatabaseExtensions'
        );
    }
}
