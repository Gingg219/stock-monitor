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
            Route::get('/locations/warehouses', [LocationController::class, 'warehouses']);
            Route::get('/locations/racks',      [LocationController::class, 'racks']);
            Route::get('/locations/tiers',      [LocationController::class, 'tiers']);
            Route::get('/locations/slots',      [LocationController::class, 'slots']);

            //Income
            Route::get('/incomes', [IncomeController::class, 'index']);
            Route::get('/api/admin/incomes/{id}', [IncomeController::class,'show']);
            Route::post('/incomes', [IncomeController::class, 'store']);

            //StorageUnit
            Route::post('/storage-unit', [StorageUnitController::class,'getAllByStatus']);
            Route::post('/storage-unit/store', [StorageUnitController::class, 'store']);
            Route::get('/storage-unit/{income_line_id}', [StorageUnitController::class, 'getLatestCode']);
            Route::post('/storage-unit/assign', [StorageUnitController::class,'assign']);
            Route::post('/storage-unit/change-location', [StorageUnitController::class,'changeLocation']);
            Route::post('/storage-unit/scan', [StorageUnitController::class,'scan']);

        });
        
        //User
        Route::group(['prefix' => 'user'], function () {
            //...
        });
    });
});
