<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware'=>'auth:api',
    'namespace'=>'Api'
], function(){

    Route::group([
        'prefix'=>'user'
    ], function(){

        Route::get('/', 'UserController@index');            
        Route::post('set-group-id', 'UserController@setGroupId');

    });

    Route::group([
        'prefix'=>'employees'
    ], function(){

        Route::get('/', 'EmployeeController@index');
        Route::get('/details', 'EmployeeController@details');
        Route::post('{row}/set-person-id', 'EmployeeController@setPersonId');
        Route::post('{row}/add-face-id', 'EmployeeController@addFaceId');
        Route::post('clear-person-ids', 'EmployeeController@clearPersonIds');

        Route::post('add-check-in', 'EmployeeController@addCheckIn');
        Route::post('add-check-out', 'EmployeeController@addCheckOut');
    });

    Route::group([
        'prefix'=>'salesman'
    ], function(){
        Route::post('set-person-id', 'SalesmanController@setPersonId');
        Route::post('add-face-id', 'SalesmanController@addFaceId');
        Route::post('clear-person-ids', 'SalesmanController@clearPersonIds');

        Route::post('add-check-in', 'SalesmanController@addCheckIn');
        Route::post('add-check-out', 'SalesmanController@addCheckOut');

        Route::post('add-meeting-check-in', 'SalesmanController@addMeetingCheckIn');
        Route::post('add-meeting-check-out', 'SalesmanController@addMeetingCheckOut');
    });


    Route::group([
        'prefix'=>'settings'
    ], function(){
        Route::get('/', 'SettingController@index');
    });

});
