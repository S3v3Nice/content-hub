<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use PasswordValidationRulesTrait;

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $attributes = [
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];

        if (!Auth::attempt($attributes, $request->get('remember', false))) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => ['email' => ['Неверный адрес эл. почты или пароль.']],
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', Rule::unique(User::class)],
            'password' => $this->getPasswordRules(),
            'password_confirmation' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $attributes = [
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];

        $user = User::create($attributes);
        Auth::login($user, $request->get('remember', false));

        return response()->json(['success' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->flush();

        return response()->json(['success' => true]);
    }
}
