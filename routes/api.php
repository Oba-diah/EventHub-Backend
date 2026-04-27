<?php

use App\Http\Controllers\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\EventController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\UserOtpController;
use App\Http\Controllers\ResendEmailVerificationController;

  // Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp',[UserOtpController::class, 'verifyOtp']);
    
// Email Verification
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);
Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'resend'])
        ->middleware(['signed', 'throttle:6,1']);
Route::post('/email/resend-verification', [ResendEmailVerificationController::class, 'resend']);
   
// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
 
Route::get('/userInfo', [AuthController::class, 'userInfo']); 

Route::post('/logout', [AuthController::class, 'logout']);    


 

Route::post('/events', [EventController::class, 'createEvent']);
Route::get('/events', [EventController::class, 'readAllEvents']);
Route::get('/events/{id}', [EventController::class, 'readEvent']);
Route::put('/events/{id}', [EventController::class, 'updateEvent']);
Route::delete('/events/{id}', [EventController::class, 'deleteEvent']);

Route::post('/users', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);     
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::post('/roles', [RoleController::class, 'createRole']);
Route::get('/roles', [RoleController::class, 'readAllRoles']);
Route::get('/roles/{id}', [RoleController::class, 'readRole']);
Route::put('/roles/{id}', [RoleController::class, 'updateRole']);
Route::delete('/roles/{id}', [RoleController::class, 'deleteRole']);

Route::post('/bookings', [BookingController::class, 'createBooking']);
Route::get('/bookings', [BookingController::class, 'readAllBookings']);
Route::get('/bookings/{id}', [BookingController::class, 'readBooking']);
Route::put('/bookings/{id}', [BookingController::class, 'updateBooking']);
Route::delete('/bookings/{id}', [BookingController::class, 'deleteBooking']);



});
?>