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
Route::middleware('auth:sanctum')
        ->post('/register', [App\Http\Controllers\API\AuthController::class, 'register'])
        ->name('register');

Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login'])
        ->name('login');

Route::get('/pin-details/{pin}', [App\Http\Controllers\API\PinController::class, 'get'])
        ->name('pin.get');

Route::middleware('auth:sanctum')
        ->get('/dashboard', [App\Http\Controllers\API\DashboardController::class, 'get'])
        ->name('dashboard.get');

Route::middleware('auth:sanctum')->post('/payment', [App\Http\Controllers\API\UpiGateWayController::class, 'initiate'])
        ->name('payment.initiate');
Route::middleware('auth:sanctum')->post('/add-balance/{user_id}', [App\Http\Controllers\API\MoneyTransferController::class, 'directTransfer'])
        ->name('add.balance');

Route::get('/payment-redirect', [App\Http\Controllers\API\UpiGateWayController::class, 'redirected'])
        ->name('payment.redirect');

Route::middleware('auth:sanctum')->post('/bill', [App\Http\Controllers\API\BillController::class, 'store'])
        ->name('bill.store');

Route::middleware('auth:sanctum')->get('/bill', [App\Http\Controllers\API\BillController::class, 'list'])
        ->name('bill.list');

// update bill request
Route::middleware('auth:sanctum')->patch('/bill/{id}', [App\Http\Controllers\API\BillController::class, 'update'])
        ->name('bill.update');

// delete bill request
Route::middleware('auth:sanctum')->delete('/bill/{id}', [App\Http\Controllers\API\BillController::class, 'delete'])
        ->name('bill.delete');

// store user
Route::middleware('auth:sanctum')->post('/user', [App\Http\Controllers\API\UserController::class, 'store'])
        ->name('user.store');

// Route::middleware('auth:sanctum')->get('/tt', [App\Http\Controllers\API\BillController::class, 'tt'])
//         ->name('bill.tt');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
