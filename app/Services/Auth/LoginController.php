<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LoginServices;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $loginService;

    public function __construct(LoginServices $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginData = $this->loginService->attemptLogin($request->only('email', 'password'));

        if (! $loginData) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = $loginData['user'];

        // Cek apakah metode getRoleNames tersedia & user punya role
        $role = method_exists($user, 'getRoleNames')
            ? ($user->getRoleNames()->first() ?? $user->role ?? 'karyawan')
            : ($user->role ?? 'karyawan');

        return response()->json([
            'token' => $loginData['token'],
            'role' => $role,
        ]);
    }
}
