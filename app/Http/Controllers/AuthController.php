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
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'nullable|string|min:8',
            'phoneNumber'  => 'nullable|string',
            'gender'       => 'nullable|string',
            'dateOfBirth'  => 'nullable|date',
            'location'     => 'nullable|string',
            'role_id'      => 'required|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'role_id'     => $validated['role_id'],
            'password'    => isset($validated['password'])
                ? Hash::make($validated['password'])
                : Hash::make('Qwerty1234'),
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'gender'      => $validated['gender'] ?? null,
            'dateOfBirth' => $validated['dateOfBirth'] ?? null,
            'location'    => $validated['location'] ?? null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $user->notify(new VerifyEmailNotification($verificationUrl));

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
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

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Please verify your email first.'
            ], 403);
        }

        $lastOtp = UserOtp::where('user_id', $user->id)->latest()->first();

        if ($lastOtp && now()->lt($lastOtp->created_at->addSeconds(60))) {
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
            'attempts'   => 0,
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
            'otp'   => 'required|numeric'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $otpRecord = UserOtp::where('user_id', $user->id)
            ->where('otp', $validated['otp'])
            ->first();

        if (!$otpRecord || now()->gt($otpRecord->expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $otpRecord->delete();

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