<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Registration
    public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255|unique:users',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|',
    ]);

    try {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'nonmember',
        ]);

        Log::info('User created successfully', ['user_id' => $user->id]);

    } catch (\Exception $e) {
        Log::error('Error creating user: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'User creation failed'], 500);
    }
    
 try {
        $token = $user->createToken('authToken')->plainTextToken;
        Log::info('Token created successfully');
    } catch (\Exception $e) {
        Log::error('Error creating token: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Token creation failed'], 500);
    }
    return response()->json([
        'status' => 'success',
        'user' => $user,
        'token' => $token,
    ], 201);
}

    // Login
    public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($request->only('username', 'password'))) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid login credentials'
        ], 401);
    }

    // Retrieve the authenticated user directly from the database
    $user = User::where('username', $request->username)->firstOrFail();
    $token = $user->createToken('authToken')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'user' => $user,
        'token' => $token,
    ], 200);
}


    public function subscribe(Request $request)
{
    $user = $request->user();
    $user->status = 'member';
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Subscription successful, status updated to member',
        'user' => $user,
    ], 200);
}

}

