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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['namespace' => 'Api', 'middleware' => 'auth:api'], function () {
    Route::apiResource('categories', 'CategoryController');
    Route::delete('categories', 'CategoryController@destroyCollection');

    Route::apiResource('genres', 'GenreController');
    Route::delete('genres', 'GenreController@destroyCollection');

    Route::apiResource('cast-members', 'CastMemberController');
    Route::delete('cast-members', 'CastMemberController@destroyCollection');

    Route::apiResource('videos', 'VideoController');
    Route::delete('videos', 'VideoController@destroyCollection');
});
