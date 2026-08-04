<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Otp;
use Carbon\Carbon;

class OtpController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Generate a 6-digit OTP
        $otpCode = (string) rand(100000, 999999);

        // Store it
        Otp::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP generated successfully'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }

        $otp = Otp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // Mark OTP as used
        $otp->update(['is_used' => true]);

        // Look up active password client from database
        $client = \DB::table('oauth_clients')
            ->where('password_client', 1)
            ->where('revoked', 0)
            ->first();

        $clientId = $client ? (string)$client->id : '1';
        $clientSecret = $client ? (string)$client->secret : 'FiqS53BcIaBRT61riijv9G83n8lxrsvbixm0zewc';

        $tokenRequest = Request::create(
            '/oauth/token',
            'POST',
            [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $request->username,
                'password' => $request->password,
                'scope' => '',
            ]
        );

        return app()->handle($tokenRequest);
    }
}
