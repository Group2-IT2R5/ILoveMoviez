<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        $token = JWTAuth::fromUser($user);

        $user->api_token = $token; // Optional
        $user->save();

        return [
            'user_id' => $user->id,
            'token'   => $token,
        ];
    }

    public function login(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['error' => 'User not found'];
        }

        if (!Hash::check($password, $user->password)) {
            return ['error' => 'Invalid password'];
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return ['error' => 'Could not create token'];
        }

        return [
            'user_id' => $user->id,
            'token'   => $token,
        ];
    }
}
