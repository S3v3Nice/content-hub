<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotVerifiedEmailRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $verified = User::where('email', $value)->whereNotNull('email_verified_at')->exists();

        if ($verified) {
            $fail('Данный E-mail уже используется и подтвержден на другом аккаунте.');
        }
    }
}
