<?php

namespace App\Http\Controllers;

trait PasswordValidationRulesTrait
{
    protected function getPasswordRules(bool $shouldBeConfirmed = true): array
    {
        $rules = ['required', 'string', 'min:8'];
        if ($shouldBeConfirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }
}
