<?php

use Illuminate\Http\Request;

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

Route::get('pick_list', function () {
    $companies = \App\Company::all();
    $positions = \App\Preset::availablePositions();
    $units = \App\Preset::availableUnits();

    return response()->json([
        'companies' => $companies,
        'positions' => $positions,
        'units' => $units
    ]);
});

Route::prefix('presets')->group(function () {
    Route::post('add', 'PresetController@store');
    Route::post('update/{preset}', 'PresetController@update');
});

Route::group(['prefix' => 'file'], function () {
    Route::post('check', 'FileController@check')->name('check');
    Route::post('upload', 'FileController@upload')->name('upload');
    Route::post('remove', 'FileController@remove')->name('remove');
});

Route::group(['prefix' => 'jobs'], function () {
    Route::post('start_process', 'JobController@startProcess')->name('start_process');
});
