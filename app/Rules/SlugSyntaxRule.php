<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SlugSyntaxRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $value)) {
            $fail('Ярлык может содержать только английские буквы, цифры, дефисы и нижние подчеркивания.');
            return;
        }

        if (preg_match('/^[_-]+$/i', $value)) {
            $fail('Ярлык не может состоять только из дефисов или нижних подчеркиваний.');
        }
    }
}
