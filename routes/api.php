<?php

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

Route::prefix('/internal')->group(function () {
    Route::post('pdf/generate', 'Api\PdfController@generate');
    Route::post('pdf', 'Api\PdfController@store');
    Route::post('pdf/template', 'Api\PdfTemplateController@store');
    Route::post('sftp-settings', 'Api\SftpSettingsController@store');
    Route::put('sftp-settings', 'Api\SftpSettingsController@update');
});

Route::prefix('/admin')->middleware('token')->group(function () {
    Route::post('pdf', 'Api\PdfController@store');
    Route::get('pdf/{campaignUuid}', 'Api\PdfController@show');
    Route::delete('pdf/{campaignUuid}', 'Api\PdfController@destroy');
    Route::put('pdf', 'Api\PdfController@updateSchema');
    Route::post('pdf/generate', 'Api\PdfController@generate')->name('pdf.generate');
    Route::post('pdf/template', 'Api\PdfTemplateController@store');
    Route::put('pdf/template', 'Api\PdfTemplateController@update');

    Route::post('sftp-settings', 'Api\SftpSettingsController@store');
    Route::put('sftp-settings', 'Api\SftpSettingsController@update');
    Route::delete('sftp-settings/{campaignUuid}', 'Api\SftpSettingsController@destroy');
    Route::get('sftp-settings/{campaignUuid}', 'Api\SftpSettingsController@show');

    Route::post('fonts', 'Api\FontController@store')->name('font.create');
    Route::get('fonts', 'Api\FontController@index')->name('font.get');
    Route::get('fonts/get', 'Api\FontController@getByName');

    Route::post('icc', 'Api\IccController@store');
    Route::get('icc', 'Api\IccController@index');
    Route::get('icc/get', 'Api\IccController@getByName');
});
