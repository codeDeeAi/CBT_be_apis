<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Create Users
    public function createUser(Request $request)
    {
        // Validation
        $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'bail|required|string',
            'gender' => 'bail|required|string|in:male,female',
            'phone' => 'bail|required|string|unique:users,phone,except,id|min:11|max:11',
            'email' => 'bail|required|email|unique:users,email,except,id',
            'password' => 'bail|required|min:6',
            'date_of_birth' => 'bail|required|date|date_format:m/d/Y',
        ]);

        // Create User
        DB::beginTransaction();
        try {
            // ** Create User
            User::create([

            ]);

            // Dispatch Email For New Accounts

            //Save to Database
            DB::commit();

            // Response
            return response('Success !', 200);
        } catch (\Throwable $th) {
            //throw $th;
            //Save to Database
            DB::rollBack();
            // Response
            return response('Failed !', 500);
        }
    }
}
