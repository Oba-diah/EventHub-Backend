<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        // Validate the request parameters
        $user = User::findOrFail($id);

        // Check if the user exists and the hash matches the email verification hash
        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.'
                ], 400);
        }
        // Check if the user's email is already verified
        if($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
                ], 200);
        }
        // Mark the user's email as verified
        $user->markEmailAsVerified();
        if ($user instanceof MustVerifyEmail) {
            event(new Verified($user));
        }

        $user->is_active = true; // Activate the user account upon successful email verification
        $user->save();
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}