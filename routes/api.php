<?php

use App\Http\Controllers\Api\Admin\IncomeController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\StorageUnitController;
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

            //Location
            Route::get('/locations', [LocationController::class, 'index'])
            ->middleware('role:admin');
            Route::put('/locations/{id}', [LocationController::class, 'update'])
            ->middleware('role:admin');

            //Income
            Route::get('/incomes', [IncomeController::class, 'index']);
            Route::get('/api/admin/incomes/{id}', [IncomeController::class,'show']);
            Route::post('/incomes', [IncomeController::class, 'store']);

            //StorageUnit
            Route::post('/storage-unit', [StorageUnitController::class, 'store']);

        });
        
        //User
        Route::group(['prefix' => 'user'], function () {
            //...
        });
    });
});
