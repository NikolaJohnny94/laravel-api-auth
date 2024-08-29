<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    public function registerNewUser(array $fields)
    {
        return User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);
    }
    public function loginUser(array $fields)
    {
        return User::where('email', $fields['email'])->first();
    }
    public function logoutUser(Request $request)
    {
        $request->user()->tokens()->delete();
    }
}
