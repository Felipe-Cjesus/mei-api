<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ApiResponse;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|confirmed|min:6|max:20',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        
        if (!$token || $token == '') {
            return response()->json(['message' => 'Could not create token'], 401);
        }

        $registerUser = [
            'user' => $user,
            'token' => $token
        ];

        return ApiResponse::success(
            $registerUser, 
            201, 
            'Registration successful'
        );
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        if (!$token || $token == '') {
            return response()->json(['message' => 'Could not create token'], 401);
        }

        $loginUser = [
            'user' => $user,
            'token' => $token
        ];

        return ApiResponse::success(
            $loginUser, 
            201, 
            'Registration successful'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
