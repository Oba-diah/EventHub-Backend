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
    $user = User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json([
            'message' => 'Invalid verification link.'
        ], 400);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email already verified.'
        ], 200);
    }

    $user->markEmailAsVerified();

    event(new Verified($user));

    $user->is_active = true;
    $user->save();

    return response()->json([
        'message' => 'Email verified successfully.'
    ], 200);
}
}