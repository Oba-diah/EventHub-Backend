<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\SendOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'nullable|string|min:8',
            'phoneNumber'     => 'nullable|string',
            'gender'          => 'nullable|string',
            'dateOfBirth'     => 'nullable|date',
            'location'        => 'nullable|string',
            'role_id'         => 'required|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'role_id'        => $validated['role_id'],
            'password'       => isset($validated['password'])
                                ? Hash::make($validated['password'])
                                : Hash::make('Qwerty1234'),
            'phoneNumber'    => $validated['phoneNumber'] ?? null,
            'gender'         => $validated['gender'] ?? null,
            'dateOfBirth'    => $validated['dateOfBirth'] ?? null,
            'location'       => $validated['location'] ?? null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $user->id,
                'hash' => sha1($user->email)
            ]
        );

        $user->notify(new VerifyEmailNotification($verificationUrl));

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user'    => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials']
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email first.'
            ], 403);
        }

        $lastOtp = UserOtp::where('user_id', $user->id)->latest()->first();

        if ($lastOtp && $lastOtp->created_at && now()->diffInSeconds($lastOtp->created_at) < 60) {
            return response()->json([
                'message' => 'Please wait before requesting another OTP'
            ], 429);
        }

        UserOtp::where('user_id', $user->id)->delete();

        $otp = rand(100000, 999999);

        UserOtp::create([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtpNotification($otp));

        return response()->json([
            'message' => 'OTP sent to your email.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $record = UserOtp::where('user_id', $user->id)->first();

        if (! $record) {
            return response()->json([
                'message' => 'OTP not found'
            ], 404);
        }

        if (now()->greaterThan($record->expires_at)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        if (($record->attempts ?? 0) >= 5) {
            return response()->json([
                'message' => 'Too many attempts'
            ], 429);
        }

        $record->increment('attempts');

        if ($record->otp != $validated['otp']) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        $record->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.'
        ]);
    }
}