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

Route::get("/json",function(){

    $job = new \StudentCentralApp\Jobs\GenerateJSONFiles();
    $job->execute();
});


//Route::group(['prefix'=>'courses'],function() {
  //  Route::get('/{term}', 'CourseDBController@index');
    // Route::get('/search/{term}', 'CourseController@search');
//});


Route::group(['prefix'=>'courses'],function() {
    Route::get('/{term}', 'CourseController@index');
    Route::get('/search/{term}', 'CourseController@search');
});

Route::get('/course', 'CourseController@course');


Route::group(['prefix'=>'crosslisted'],function() {
    Route::get('/', 'CrossListedCoursesController@index');

});

// API routes
Route::group(['prefix'=>'api'],function(){

    Route::get('/terms', 'TermsController@index');
    Route::get('/terms/paginate', 'TermsController@paginate');


    // Grade distribution api
    Route::group(['prefix'=>'grades'],function(){

        Route::get('/', 'GradeDistributionApiController@search');
        Route::get('/acadTerms', 'GradeDistributionApiController@acadTerms');
        Route::get('/departments', 'GradeDistributionApiController@departments');
        Route::get('/reportTypes', 'GradeDistributionApiController@reportTypes');
        Route::get('/schools', 'GradeDistributionApiController@schools');



    });

    // Non standard session data
    Route::group(['prefix'=>'nonStandardSession'],function(){

        Route::get('/', 'NonStandardSessionApiController@search');
        Route::get('/acadTerms', 'NonStandardSessionApiController@acadTerms');
        Route::get('/schools', 'NonStandardSessionApiController@schools');
        Route::get('/departments', 'NonStandardSessionApiController@departments');

    });


    // Non standard session data
    Route::group(['prefix'=>'contactForm'],function(){

        Route::get('/user/{username}', 'ContactFormApiController@getUser');
        Route::post('/', 'ContactFormApiController@submit');
        Route::get("/topics",'ContactFormApiController@getTopics');


    });

    Route::group(['prefix'=>'courses'],function(){

        Route::get('/genEdReq', 'CourseApiController@genEdRequirements');
        Route::get('/departments/{term}', 'CourseApiController@departments');
        Route::get('/instructionModes', 'CourseApiController@instructionMode');


    });



});
