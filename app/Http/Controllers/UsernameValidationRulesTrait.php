<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\UsernameSyntaxRule;
use Illuminate\Validation\Rule;

trait UsernameValidationRulesTrait
{
    protected function getUsernameRules(): array
    {
        return ['required', 'string', Rule::unique(User::class), 'min:3', 'max:20', new UsernameSyntaxRule()];
    }
}