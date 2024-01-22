<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UsernameSyntaxRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $value)) {
            $fail('Имя пользователя может содержать только английские буквы, цифры, дефисы и нижние подчеркивания.');
            return;
        }

        if (preg_match('/^[_-]+$/i', $value)) {
            $fail('Имя пользователя не может состоять только из дефисов или нижних подчеркиваний.');
        }
    }
}
