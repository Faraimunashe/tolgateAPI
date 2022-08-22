<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $user->attachRole('user');

        $token = $user->createToken('tolgateapptoken')->plainTextToken;

        $account = Account::create([
            'user_id' => $user->id
        ]);

        $response = [
            'user' => $user,
            'token' => $token,
            'account' => $account,
            'error'=> false
        ];

        return response($response, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //check by email
        $user = User::where('email', $fields['email'])->first();

        //check password
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message' => 'Invalid login creditials',
                'error'=> true
            ], 401);
        }

        $token = $user->createToken('tolgateapptoken')->plainTextToken;
        $account = Account::where('user_id', $user->id)->first();

        $response = [
            'user' => $user,
            'token' => $token,
            'account' => $account,
            'error'=> false
        ];

        return response($response, 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'logged out successfully',
            'error'=> false
        ];
    }
}
