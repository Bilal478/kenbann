<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Manually validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Validation passed, attempt authentication
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $token = $request->user()->createToken('token-name')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    // Authentication failed
    return response()->json(['message' => 'Unauthorized'], 401);

    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create($data);

        $token = $user->createToken('token-name')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
