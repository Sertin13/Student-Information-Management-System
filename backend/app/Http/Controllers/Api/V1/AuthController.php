<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = \App\Models\User::with('role')->where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['success' => true, 'data' => ['token' => $token, 'user' => $user]]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()->load('role')]);
    }
}
