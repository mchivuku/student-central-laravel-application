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


ini_set('display_errors',1);
error_reporting(-1);

Route::get('/', function () {

    $job = new \StudentCentralCourseBrowser\Jobs\CreateSC911LE3();
    $job->execute();

    //return view('welcome');
});
