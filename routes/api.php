<?php

use App\Http\Controllers\Api\Admin\LocationController;
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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $r) => $r->user());

});

Route::middleware('api')->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {
        
        //Admin
        Route::group(['prefix' => 'admin'], function () {
            Route::get('/locations', [LocationController::class, 'index'])
            ->middleware('role:admin|user');
        });
        
        //User
        Route::group(['prefix' => 'user'], function () {
            //...
        });
    });
});
