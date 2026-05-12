<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\SendOtpNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'phoneNumber' => 'nullable|string',
            'gender' => 'nullable|string',
            'dateOfBirth' => 'nullable|date',
            'location' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => isset($validated['password'])
                ? Hash::make($validated['password'])
                : Hash::make('Qwerty1234'),
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dateOfBirth' => $validated['dateOfBirth'] ?? null,
            'location' => $validated['location'] ?? null,
        ]);

        $verificationUrl = config('app.frontend_url')
            . '/verify-email?user=' . $user->id
            . '&hash=' . sha1($user->email);

        $user->notify(new VerifyEmailNotification($verificationUrl));

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Please verify your email first'
            ], 403);
        }

        UserOtp::where('user_id', $user->id)->delete();

        $otp = rand(100000, 999999);

        UserOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtpNotification($otp));

        return response()->json([
            'status' => 'otp_sent',
            'message' => 'OTP sent to your email',
            'email' => $user->email
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $otpRecord = UserOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'message' => 'OTP not found'
            ], 404);
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 403);
        }

        if (! Hash::check($validated['otp'], $otpRecord->otp)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 403);
        }

        UserOtp::where('user_id', $user->id)->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'user' => 'required|integer',
            'hash' => 'required|string',
        ]);

        $user = User::find($request->user);

        if (! $user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (sha1($user->email) !== $request->hash) {
            return response()->json([
                'message' => 'Invalid verification link'
            ], 403);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logout successful.'
        ]);
    }
}