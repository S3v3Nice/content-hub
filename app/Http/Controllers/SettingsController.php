<?php

namespace App\Http\Controllers;

use App\Rules\NotVerifiedEmailRule;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    use ApiJsonResponse;
    use PasswordValidationRulesTrait;
    use UsernameValidationRulesTrait;

    public function sendEmailVerificationLink(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->errorJsonResponse('E-mail данного пользователя уже подтверждён.');
        }

        $user->sendEmailVerificationNotification();

        return $this->successJsonResponse('Ссылка для подтверждения E-mail отправлена на ' . $user->email . '.');
    }

    public function changeProfileSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['string', 'nullable'],
            'last_name' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->update($validator->getData());

        return $this->successJsonResponse('Настройки профиля изменены.');
    }

    public function changeUsername(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => $this->getUsernameRules(),
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->username = $request->get('username');
        $user->save();

        return $this->successJsonResponse('Имя пользователя изменено.');
    }

    public function changeEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'confirmed', new NotVerifiedEmailRule()],
            'email_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $email = $request->get('email');

        $user = Auth::user();
        $user->email = $email;
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return $this->successJsonResponse('Ссылка для подтверждения E-mail отправлена на ' . $email . '.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => $this->getPasswordRules(),
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->password = $request->get('password');
        $user->save();

        return $this->successJsonResponse('Пароль изменён.');
    }
}
