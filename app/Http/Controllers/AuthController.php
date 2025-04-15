<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token,
        ]);
    }

    public function me(Request$request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }

    public function showSetPasswordForm(Request $request)
    {
        if(!$request->hasValidSignature()){
            return response()->json(['message'=>'Invalid or expired link'],403);
        }

        $email = $request->query('email');
        $user = User::where('email', $email)->first();

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['email'=>$email]);
    }

    public function setPassword(Request $request)
    {
        if(!$request->hasValidSignature()){
            return response()->json(['message'=>'Invalid or expired link'],403);
        }

        $request->validate([
            'email'=>'required|mail',
            'password'=>'required|string|min:8|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json(['message', 'User not found'], 404);
        }

        $user->password(Hash::make($request->password));
        $user->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Password set successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
