<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;
use App\Services\Auth\LoginServices;
use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{


    protected LoginServices $loginService;

    public function __construct(LoginServices $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->loginService->attemptLogin($request->validated());

        if (! $result) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Revoke all previous tokens for this user to avoid multiple active tokens
        // $tokens = $result['user']->tokens;
        // foreach ($tokens as $token) {
        //     $token->revoke();
        // }

        return new LoginResource($result);
    }
}
