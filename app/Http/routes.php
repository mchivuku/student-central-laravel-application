<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Log;

ini_set('display_errors',1);
error_reporting(-1);

Route::get('/', function () {
    Log::error('Test');
    return view('welcome');
});
