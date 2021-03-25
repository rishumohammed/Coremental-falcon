<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@index')->name('home');

Route::group([
    'middleware'=>['auth', 'admin'],
    'namespace'=>'Admin',
    'prefix'=>'admin'
], function(){
    Route::get('/', 'HomeController@index');

    Route::group([
        'prefix'=>'employees'
    ], function(){
        Route::get('/', 'EmployeeController@index');
        Route::get('/create', 'EmployeeController@create');
        Route::post('/create', 'EmployeeController@store');
        Route::get('/edit/{row}', 'EmployeeController@edit');
        Route::post('/edit/{row}', 'EmployeeController@update');
        Route::get('/delete/{row}', 'EmployeeController@delete');

        Route::get('/attendance', 'EmployeeController@attendance');
    }); 
    
    Route::group([
        'prefix'=>'salesman'
    ], function(){        
        Route::get('/meeting-attendance', 'SalesmanController@meetingAttendance');
    }); 

    Route::group([
        'prefix'=>'users'
    ], function(){
        Route::get('/', 'UserController@index');
        Route::get('/create', 'UserController@create');
        Route::post('/create', 'UserController@store');
        Route::get('/edit/{row}', 'UserController@edit');
        Route::post('/edit/{row}', 'UserController@update');
        Route::get('/delete/{row}', 'UserController@delete');

        Route::get('/{row}/assigned-employees', 'UserController@assignedEmployees');
        Route::post('/{user}/assigned-employees', 'UserController@assignEmployee');
        Route::get('/{user}/assigned-employees/unassign/{employee_id}', 'UserController@unassignEmployee');
    });   
    
    Route::group([
        'prefix'=>'settings'
    ], function(){
        Route::get('/', 'SettingController@index');        
        Route::post('/', 'SettingController@update');
    }); 

});


Route::get('/install5432', function(){
    define('STDIN',fopen("php://stdin","r"));
    //\Artisan::call('migrate:refresh', ['--path'=>'database/migrations/2020_10_07_062828_create_meeting_attendances_table.php']);
    //\Artisan::call('migrate:refresh', ['--path'=>'database/migrations/2020_09_27_192122_create_attendances_table.php']);
    //\Artisan::call('migrate:refresh', ['--path'=>'database/migrations/2020_10_01_191234_create_settings_table.php']);
    //\Artisan::call('db:seed', ['--class'=>'SettingsSeeder']);
    //\Artisan::call('migrate:refresh', ['--seed' => true]);
    //Artisan::call('migrate:refresh', ['--path' => 'vendor/laravel/passport/database/migrations']);
    //\Artisan::call('passport:keys');
    //\Artisan::call('passport:client', [
    //    '--password' => true
     //]);
});
