<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultAuthenticationController extends Controller
{
    // Authenticate Users
    public function login(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check User
        if (User::where('email', $request->email)->exists()) {
            // Get User
            $user = User::where('email', $request->email)->first();

            // Check Password
            if (Hash::check($request->password, $user->password)) {
                // Create Token
                $token = $user->createToken($request->userAgent());

                // Return Success
                return response()->json([
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                    'token' => $token->plainTextToken,
                ], 200);
            }

            // Invalid Details
            return response()->json([
                'message' => 'Incorrect login details !'
            ], 400);
        }
        // User Doesnt Exist
        return response()->json([
            'message' => 'This credentials do not match our records !'
        ], 500);
    }
}
