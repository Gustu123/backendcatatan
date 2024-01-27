<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No user with this email.'],
            ]);
        }

        return response()->json([
            "data" => [
                "user" => $user
            ]
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => ['nullable', 'min:8', 'max:16'],
            'newPassword' => ['required', 'min:8', 'max:16']
        ]);

        $user = User::find($request->userId);

        if ($request->oldPassword) {
            if (!Hash::check($request->oldPassword, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['The old password is incorrect.'],
                ]);
            }
        }

        $user->password = bcrypt($request->newPassword);

        $user->save();

        return response()->json();
    }
    
    public function show(string $id)
    {
        $user = User::find($id);

        return response()->json([
            "data" => [
                "user" => $user
            ]
        ]);
    }
}
