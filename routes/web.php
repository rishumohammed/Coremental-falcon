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

        Route::get('/{row}/blocks', 'EmployeeController@blocks');
        Route::post('/{row}/blocks', 'EmployeeController@storeBlock');
        Route::get('/blocks/delete/{block}', 'EmployeeController@deleteBlock');

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
        Route::get('/general', 'SettingController@general');        
        Route::post('/general', 'SettingController@update');
        Route::post('/clear-excel-cache', 'SettingController@clearExcelCache');

        Route::get('/weekend', 'SettingController@weekend');
        Route::post('/weekend', 'SettingController@updateWeekend');

        Route::get('/holidays', 'SettingController@holidays');
        Route::post('/holidays', 'SettingController@storeHoliday');
        Route::get('/holidays/delete/{id}', 'SettingController@deleteHoliday');
        Route::get('/holidays/restore/{id}', 'SettingController@restoreHoliday');
        Route::get('/holidays/force-delete/{id}', 'SettingController@forceDeleteHoliday');
    }); 

    Route::group([
        'prefix'=>'leave-types'
    ], function(){
        Route::get('/', 'LeaveTypeController@index');        
        Route::get('/create', 'LeaveTypeController@create');        
        Route::post('/create', 'LeaveTypeController@store');        
        Route::get('/edit/{leaveType}', 'LeaveTypeController@edit');        
        Route::post('/edit/{leaveType}', 'LeaveTypeController@update');        
        Route::get('/delete/{leaveType}', 'LeaveTypeController@delete');        
        Route::get('/set-default/{leaveType}', 'LeaveTypeController@setDefault');        
    }); 

    Route::group([
        'prefix'=>'departments'
    ], function(){
        Route::get('/', 'DepartmentController@index');        
        Route::get('/create', 'DepartmentController@create');        
        Route::post('/create', 'DepartmentController@store');        
        Route::get('/edit/{department}', 'DepartmentController@edit');        
        Route::post('/edit/{department}', 'DepartmentController@update');        
        Route::get('/delete/{department}', 'DepartmentController@delete');        
    }); 

    Route::group([
        'prefix'=>'designations'
    ], function(){
        Route::get('/', 'DesignationController@index');        
        Route::get('/create', 'DesignationController@create');        
        Route::post('/create', 'DesignationController@store');        
        Route::get('/edit/{designation}', 'DesignationController@edit');        
        Route::post('/edit/{designation}', 'DesignationController@update');        
        Route::get('/delete/{designation}', 'DesignationController@delete');        
    }); 

    Route::group([
        'prefix'=>'shifts'
    ], function(){
        Route::get('/', 'ShiftController@index');        
        Route::get('/create', 'ShiftController@create');        
        Route::post('/create', 'ShiftController@store');        
        Route::get('/edit/{shift}', 'ShiftController@edit');        
        Route::post('/edit/{shift}', 'ShiftController@update');        
        Route::get('/delete/{shift}', 'ShiftController@delete');        
    }); 

    Route::group([
        'prefix'=>'locations'
    ], function(){
        Route::get('/', 'LocationController@index');        
        Route::get('/create', 'LocationController@create');        
        Route::post('/create', 'LocationController@store');        
        Route::get('/edit/{location}', 'LocationController@edit');        
        Route::post('/edit/{location}', 'LocationController@update');        
        Route::get('/delete/{location}', 'LocationController@delete');        
    }); 

    Route::group([
        'prefix'=>'reports'
    ], function(){
        Route::get('/working-hours', 'ReportController@workingHours');
        Route::get('/absent', 'ReportController@absent');
        Route::get('/leaves', 'ReportController@leaves');
        Route::get('/missing-checkouts', 'ReportController@missingCheckouts');
        Route::post('/manual-checkout', 'ReportController@addManualCheckout');
        Route::post('/assign-leave', 'ReportController@assignLeave');
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
    \Artisan::call('migrate', ['--path'=>'database/migrations/2026_06_19_100000_create_holidays_table.php']);
    \Artisan::call('migrate', ['--path'=>'database/migrations/2026_06_19_100001_add_weekend_days_to_settings.php']);
    \Artisan::call('migrate', ['--path'=>'database/migrations/2026_06_19_110000_add_soft_deletes_to_holidays_table.php']);
    echo "Done.";
});
