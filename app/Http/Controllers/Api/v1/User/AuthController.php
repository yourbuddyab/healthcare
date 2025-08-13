<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }
}
