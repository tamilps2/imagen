<?php

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

Route::get('/', function () {
    return redirect(route('home'));
});

Route::get('/test', function () {
    phpinfo();
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'jobs',  'middleware' => 'auth'], function() {

    Route::get('/', 'JobController@index')->name('jobs');

    Route::get('create', 'JobController@create')->name('create_job');
    Route::get('view/{job}', 'JobController@show')->name('view_job');

    Route::get('process', 'JobController@startProcess')->name('start_process');
    Route::post('process', 'JobController@process')->name('process');
    Route::get('progress', 'JobController@progress')->name('progress');

    Route::post('delete/{job}', 'JobController@destroy')->name('delete_job');

});

Route::group(['prefix' => 'presets',  'middleware' => 'auth'], function() {

    Route::get('/', 'PresetController@index')->name('presets');

    Route::get('create', 'PresetController@create')->name('create_preset');
    Route::post('add', 'PresetController@store')->name('add_preset');

    Route::get('edit/{preset}', 'PresetController@edit')->name('edit_preset');
    Route::post('update/{preset}', 'PresetController@update')->name('update_preset');

    Route::post('delete/{preset}', 'PresetController@destroy')->name('remove_preset');

});

Route::group(['prefix' => 'companies',  'middleware' => 'auth'], function() {

    Route::get('/', 'CompanyController@index')->name('companies');

    Route::get('/create', 'CompanyController@create')->name('create_company');
    Route::post('/store', 'CompanyController@store')->name('store_company');

    Route::get('/edit/{company}', 'CompanyController@edit')->name('edit_company');
    Route::post('/update/{company}', 'CompanyController@update')->name('update_company');

    Route::post('/delete/{company}', 'CompanyController@destroy')->name('destroy_company');

    Route::get('authorize', 'CompanyController@authorizeGoogle')->name('authorize');
    Route::get('revoke/{company}', 'CompanyController@revokeGoogle')->name('revoke');

});
