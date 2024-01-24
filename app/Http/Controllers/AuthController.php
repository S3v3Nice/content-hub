<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NotVerifiedEmailRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiJsonResponse;
    use PasswordValidationRulesTrait;
    use UsernameValidationRulesTrait;

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $attributes = $request->only('username', 'password');

        if (!Auth::attempt($attributes, $request->get('remember', true))) {
            return $this->errorJsonResponse('Неверное имя пользователя или пароль.');
        }

        return $this->successJsonResponse();
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'              => $this->getUsernameRules(),
            'email'                 => ['required', 'email', new NotVerifiedEmailRule()],
            'password'              => $this->getPasswordRules(),
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $attributes = $request->only('username', 'email', 'password');

        $user = User::create($attributes);
        $user->sendEmailVerificationNotification();
        Auth::login($user, $request->get('remember', true));

        return $this->successJsonResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->flush();

        return $this->successJsonResponse();
    }

    public function verifyEmail(Request $request, string $id): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return $this->errorJsonResponse('Недействительная ссылка подтверждения E-mail адреса.');
        }

        $user = User::find($id);

        if ($user->hasVerifiedEmail()) {
            return $this->errorJsonResponse('E-mail адрес пользователя ' . $user->username . ' уже подтвержден.');
        }

        $validator = Validator::make(
            ['email' => $user->email],
            ['email' => [new NotVerifiedEmailRule()]]
        );

        if ($validator->fails()) {
            return $this->errorJsonResponse(
                'E-mail адрес ' . $user->email . ' уже подтвержден у другого пользователя.'
            );
        }

        if (!$user->markEmailAsVerified()) {
            return $this->errorJsonResponse('Не получилось внести изменения в базе данных.');
        }

        return $this->successJsonResponse();
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', Rule::exists(User::class, 'email')],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $email = $request->get('email');

        // TODO: implement sending password reset link

        return $this->successJsonResponse();
    }
}
