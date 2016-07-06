<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/26/16
 */


namespace StudentCentralApp\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Custom macros to help with filtering /paging the results
 * of the collection
 */
class CourseCollectionExtensions extends ServiceProvider
{

    public function boot(){

        Collection::macro('pipe', function ($callback) {
            return $callback($this);
        });

        Collection::macro('dd', function (\Closure $callback = null) {

            if ($callback instanceof \Closure) {

                dd(call_user_func($callback, $this));

            }

            dd($this);

        });
    }
}
