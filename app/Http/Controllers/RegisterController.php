<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name"     => ['required', 'max:255'],
            "email"    => ['required', 'unique:users,email'],
            "password" => ['required', 'min:8', 'max:16'],
            "username" => ['required', 'unique:users,username'],
            "phone"    => ['required']
        ]);

        $data = [
            "name"     => $validated['name'],
            "email"    => $validated['email'],
            "password" => bcrypt($validated['password']),
            "username" => $validated['username'],
            "phone"    => $validated['phone']
        ];

        $user = User::create($data);

        return response()->json([], 201);
    }
}
