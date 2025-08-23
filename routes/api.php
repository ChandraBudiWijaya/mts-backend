<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\GeofenceController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\WorkPlanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute-rute ini tidak memerlukan autentikasi.
|
*/

Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Semua rute di dalam grup ini dilindungi oleh Sanctum dan memerlukan
| Bearer Token yang valid untuk bisa diakses.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // --- AUTHENTICATION ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles'); // Eager load roles user
    });

    // --- EMPLOYEE MANAGEMENT ---
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('can:view-employees');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('can:create-employees');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('can:view-employees');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('can:edit-employees');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('can:edit-employees');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('can:delete-employees');

    // --- GEOFENCE MANAGEMENT ---
    Route::get('/geofences', [GeofenceController::class, 'index'])->middleware('can:view-geofences');
    Route::post('/geofences', [GeofenceController::class, 'store'])->middleware('can:create-geofences');
    Route::get('/geofences/{geofence}', [GeofenceController::class, 'show'])->middleware('can:view-geofences');
    Route::put('/geofences/{geofence}', [GeofenceController::class, 'update'])->middleware('can:edit-geofences');
    Route::patch('/geofences/{geofence}', [GeofenceController::class, 'update'])->middleware('can:edit-geofences');
    Route::delete('/geofences/{geofence}', [GeofenceController::class, 'destroy'])->middleware('can:delete-geofences');

    // --- DATA SYNCHRONIZATION ---
    Route::post('/sync/dwh-locations', [SyncController::class, 'runDwhSync'])->middleware('can:sync-geofences');

    // --- WORK PLAN MANAGEMENT ---
    Route::get('/work-plans', [WorkPlanController::class, 'index'])->middleware('can:view-work-plans');
    Route::post('/work-plans', [WorkPlanController::class, 'store'])->middleware('can:create-work-plans');
    Route::get('/work-plans/{workPlan}', [WorkPlanController::class, 'show'])->middleware('can:view-work-plans');
    Route::put('/work-plans/{workPlan}', [WorkPlanController::class, 'update'])->middleware('can:edit-work-plans');
    Route::delete('/work-plans/{workPlan}', [WorkPlanController::class, 'destroy'])->middleware('can:delete-work-plans');
    Route::post('/work-plans/{workPlan}/approve', [WorkPlanController::class, 'approve'])->middleware('can:approve-work-plans');

    // --- Rute untuk fitur baru bisa ditambahkan di sini ---

});
