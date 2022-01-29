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
    // Generate Token
    public function createToken()
    {
        // Generate token 
        $token = Str::random(64);

        // Make sure token doesn't exist
        if (UserToken::where('token', $token)->exists()) {
            // Generate another token
            $token = Str::random(64);

            return $token;
        }

        // Return Token
        return $token;
    }
    // Authenticate Administrators
    public function admin(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check User
        if (User::where('email', $request->email)->where('user_type', 'admin')->exists()) {
            // Get User
            $user = User::where('email', $request->email)->first();

            // Check Password
            if (Hash::check($request->password, $user->password)) {
                // Get Role
                $get_role_id = UserRole::where('user_id', $user->id)->first();
                $get_role = Role::where('id', $get_role_id[0]->id)->get();
                // Create Token
                $token = $this->createToken();
                // Save Token
                UserToken::create([
                    'user_id' => $user->id,
                    'name' => 'default',
                    'token' => $token,
                    // 'details',
                    // 'abilities',
                    // 'last_used_at'
                ]);

                // Return Success
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'permission' => $get_role[0]->permissions
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
    // Authenticate Users
    public function user(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check User
        if (User::where('email', $request->email)->where('user_type', 'user')->exists()) {
            // Get User
            $user = User::where('email', $request->email)->first();

            // Check Password
            if (Hash::check($request->password, $user->password)) {
                // Create Token
                $token = $this->createToken();
                // Save Token
                UserToken::create([
                    'user_id' => $user->id,
                    'name' => 'default',
                    'token' => $token,
                    // 'details',
                    // 'abilities',
                    // 'last_used_at'
                ]);

                // Return Success
                return response()->json([
                    'user' => $user,
                    'token' => $token,
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
