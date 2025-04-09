<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{

    //"forget password" button triggered
    public function sendResetLink(Request $request)
    {
        $request->validate(['email'=>'required|email']);

        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset password link sent'])
            : response()->json(['message' => 'Failed to send link'], 400);

    }

    //reset password form sent
    public function resetPassword(Request $request)
    {
        $request->validate([
           'email'=>'required|email',
           'password' => 'required|confirmed',
            'token'=>'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password){
                $user->forceFill([
                    'password'=> Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();

            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Successful password reset'])
            : response()->json(['message' => 'Failed to reset password'], 400);
    }
}
