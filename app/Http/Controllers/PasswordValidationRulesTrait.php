<?php

namespace App\Http\Controllers;

trait PasswordValidationRulesTrait
{
    protected function getPasswordRules(): array
    {
        return ['required', 'string', 'min:8', 'confirmed'];
    }
}