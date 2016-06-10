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

        /** App Bind Transformers - for dependency injection */
        $this->app->bind(
            'CourseTransformer',
            'StudentCentralCourseBrowser\Transformers\CourseTransformer'
        );

        $this->app->bind(
            'ClassTransformer',
            'StudentCentralCourseBrowser\Transformers\ClassTransformer'
        );

        $this->app->bind(
            'ClassDetailsTransformer',
            'StudentCentralCourseBrowser\Transformers\ClassDetailsTransformer'
        );


    }
}
