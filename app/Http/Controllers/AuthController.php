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
    use UsernameValidationRulesTrait;

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'    => ['required'],
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

        $login      = $request->get('login');
        $loginType  = str_contains($login, '@') ? 'email' : 'username';
        $attributes = [
            $loginType => $login,
            'password' => $request->get('password'),
        ];

        if (!Auth::attempt($attributes, $request->get('remember', true))) {
            return response()->json(
                [
                    'success' => false,
                    'message'  => 'Неверный логин или пароль.',
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'              => $this->getUsernameRules(),
            'email'                 => ['required', 'email', Rule::unique(User::class)],
            'password'              => $this->getPasswordRules(),
            'password_confirmation' => ['required'],
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
            'username' => $request->get('username'),
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];

        User::create($attributes);

        // TODO: implement sending email verification link

        return response()->json(
            [
                'success' => true,
                'message' => 'Регистрация успешна. Теперь вы можете войти в аккаунт.',
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->flush();

        return response()->json(['success' => true]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', Rule::exists(User::class, 'email')],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $email = $request->get('email');

        // TODO: implement sending password reset link

        return response()->json(
            [
                'success' => true,
                'message' => 'Ссылка для сброса пароля отправлена на ' . $email . '.',
            ]
        );
    }
}
