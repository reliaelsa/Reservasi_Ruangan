<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        // assign role karyawan (spatie)

        $user->assignRole('karyawan');
        $user->role = 'karyawan';
        $user->save();

        $token = $user->createToken('login token')->accessToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

}

