<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'username'    => 'required',
            'password'    => 'required',
            'device_name' => 'required',
        ]);
     
        $user = User::where('username', $request->username)->first();
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token =  $user->createToken($request->device_name)->plainTextToken;
     
        return response()->json([
            "data"=>[
                "token" => $token,
                "user_id" => $user->id
            ]
            ]);
    }

    // public function change_password(Request $request){
    //     $user = User::find(Auth::id());
    //     if (!empty($user)) {
    //         if (Hash::check($request->old_password, $user->password)) {
    //             $user->password = Hash::make($request->password);
    //             $user->save();
    //             return response()->json(['message' => 'success', 'data' => $user], 200);
    //         } else {
    //             return response()->json(['message' => 'not found', 'data' => ""], 404);
    //         }
    //     } else {
    //         return response()->json(['message' => 'failed', 'data' => ''], 400);
    //     };
    // }
}

