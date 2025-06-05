<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $data = $user->toArray();
        $data['profilePicture'] = $user->profile_picture
            ? url(Storage::url($user->profile_picture))
            : null;
        return response()->json([
            'user' => $data,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $data = $user->toArray();
        $data['profilePicture'] = $user->profile_picture
            ? url(Storage::url($user->profile_picture))
            : null;
        return response()->json($data);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function showSetPasswordForm(Request $request)
    {
        $email = $request->query('email');
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        Log::info('SetPassword GET params:', $request->query());

        // Проверяем подпись
        $expectedSignature = hash_hmac('sha256', http_build_query([
            'email' => urldecode($email),
            'expires' => $expires,
        ], '', '&', PHP_QUERY_RFC3986), config('app.key'));

        if (!hash_equals($expectedSignature, $signature) || $expires < now()->timestamp) {
            Log::warning('Invalid or expired signature for set-password GET', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $user = User::where('email', urldecode($email))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['email' => urldecode($email)]);
    }

    public function setPassword(Request $request)
    {
        $email = $request->input('email');
        $expires = $request->input('expires');
        $signature = $request->input('signature');

        Log::info('SetPassword POST params:', $request->all());

        // Проверяем подпись
        $expectedSignature = hash_hmac('sha256', http_build_query([
            'email' => $email,
            'expires' => $expires,
        ], '', '&', PHP_QUERY_RFC3986), config('app.key'));

        if (!hash_equals($expectedSignature, $signature) || $expires < now()->timestamp) {
            Log::warning('Invalid or expired signature for set-password POST', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $token = $user->createToken('auth-token')->plainTextToken;
        $data = $user->toArray();
        $data['profilePicture'] = $user->profile_picture
            ? url(Storage::url($user->profile_picture))
            : null;
        return response()->json([
            'message' => 'Password set successfully',
            'user' => $data,
            'token' => $token,
        ]);
    }
}