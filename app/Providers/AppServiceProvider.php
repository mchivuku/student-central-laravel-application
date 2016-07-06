<?php

namespace StudentCentralApp\Providers;

use Illuminate\Support\ServiceProvider;

/***
 * Class AppServiceProvider
 * @package StudentCentralApp\Providers
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
            'StudentCentralApp\Jobs\DatabaseExtensions'
        );

        /** App Bind Transformers - for dependency injection */
        $this->app->bind(
            'CourseTransformer',
            'StudentCentralApp\Transformers\CourseTransformer'
        );

        $this->app->bind(
            'ClassTransformer',
            'StudentCentralApp\Transformers\ClassTransformer'
        );

        $this->app->bind(
            'ClassDetailsTransformer',
            'StudentCentralApp\Transformers\ClassDetailsTransformer'
        );



    }
}
