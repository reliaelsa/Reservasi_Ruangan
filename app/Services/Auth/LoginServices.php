<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginServices
{
    public function attemptLogin(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
