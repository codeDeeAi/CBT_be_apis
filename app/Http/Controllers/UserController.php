<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Create Users
    public function create(Request $request)
    {
        // Validation
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'bail|required|email|unique:users,email,except,id',
            'password' => 'bail|required|string|min:6'
        ]);

        // Create User
        DB::beginTransaction();
        try {
            // ** Create User
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
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
