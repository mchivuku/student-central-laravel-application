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


// API routes
Route::group(['prefix'=>'api'],function(){

    // Grade distribution api
    Route::group(['prefix'=>'grades'],function(){

        Route::get('/', 'GradeDistributionApiController@search');
        Route::get('/acadTerms', 'GradeDistributionApiController@acadTerms');
        Route::get('/departments', 'GradeDistributionApiController@departments');

    });

    // Non standard session data
    Route::group(['prefix'=>'nonStandardSession'],function(){

        Route::get('/', 'NonStandardSessionApiController@search');
        Route::get('/acadTerms', 'NonStandardSessionApiController@acadTerms');
        Route::get('/schools', 'NonStandardSessionApiController@schools');
        Route::get('/departments', 'NonStandardSessionApiController@departments');

    });

});
