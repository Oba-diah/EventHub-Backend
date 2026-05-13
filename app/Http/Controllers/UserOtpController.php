<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserOtpController extends Controller
{
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string'
        ]);

        $user = User::where('email', trim($request->email))->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $otpEntry = UserOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$otpEntry) {
            return response()->json([
                'message' => 'OTP not found'
            ], 400);
        }

        if ($otpEntry->expires_at && now()->greaterThan($otpEntry->expires_at)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        if (!Hash::check($request->otp, $otpEntry->otp)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        $otpEntry->delete();

        $user->tokens()->delete();

        $token = $user->createToken('event-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'token' => $token,
            'user'  => $user
        ], 200);
    }
}