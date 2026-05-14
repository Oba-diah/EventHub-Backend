<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserOtpController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [UserOtpController::class, 'verifyOtp']);

Route::get('/events', [EventController::class, 'readAllEvents']);
Route::get('/events/{id}', [EventController::class, 'readEvent']);


Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/user', [AuthController::class, 'userInfo']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Events
    Route::post('/events', [EventController::class, 'createEvent']);
    Route::put('/events/{id}', [EventController::class, 'updateEvent']);
    Route::delete('/events/{id}', [EventController::class, 'deleteEvent']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'readUserBookings']);
    Route::get('/bookings/all', [BookingController::class, 'readAllBookings']);
    Route::post('/bookings', [BookingController::class, 'createBooking']);
    Route::get('/bookings/{id}', [BookingController::class, 'readBooking']);
    Route::put('/bookings/{id}', [BookingController::class, 'updateBooking']);
    Route::delete('/bookings/{id}', [BookingController::class, 'deleteBooking']);

    // Roles
    Route::get('/roles', [RoleController::class, 'readAllRoles']);
    Route::post('/roles', [RoleController::class, 'createRole']);
    Route::get('/roles/{id}', [RoleController::class, 'readRole']);
    Route::put('/roles/{id}', [RoleController::class, 'updateRole']);
    Route::delete('/roles/{id}', [RoleController::class, 'deleteRole']);
});