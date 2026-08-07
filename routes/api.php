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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| SBRS Hostel API (consumed by the SBRS student portal)
|--------------------------------------------------------------------------
| Exposes remedial student (bed_type = 3) hostel operations to the
| School of Basic and Remedial Studies portal.
*/
Route::prefix('v1/hostel')->middleware(['hostel.api'])->group(function () {
    Route::get('overview', [\App\Http\Controllers\Api\HostelApiController::class, 'overview']);
    Route::get('blocks', [\App\Http\Controllers\Api\HostelApiController::class, 'blocks']);
    Route::get('rooms', [\App\Http\Controllers\Api\HostelApiController::class, 'rooms']);
    Route::get('beds', [\App\Http\Controllers\Api\HostelApiController::class, 'beds']);
    Route::post('reserve', [\App\Http\Controllers\Api\HostelApiController::class, 'reserve']);
    Route::get('status', [\App\Http\Controllers\Api\HostelApiController::class, 'status']);
    Route::post('release', [\App\Http\Controllers\Api\HostelApiController::class, 'release']);
    Route::post('confirm-payment', [\App\Http\Controllers\Api\HostelApiController::class, 'confirmPayment']);
});
