<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            $result = $this->authService->register($request->all());

            return response()->json([
                'message' => 'User registered successfully',
                'user_id' => $result['user_id'],
                'token'   => $result['token'],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function login(Request $request)
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (isset($result['error'])) {
            $code = $result['error'] === 'User not found' ? 404 : 401;
            return response()->json(['error' => $result['error']], $code);
        }

        return response()->json([
            'user_id' => $result['user_id'],
            'token'   => $result['token'],
        ]);
    }
}
