<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\UsernameSyntaxRule;
use Illuminate\Validation\Rule;

trait UsernameValidationRulesTrait
{
    protected function getUsernameRules(bool $isRequired = true, ?User $user = null): array
    {
        $uniqueUsernameRule = Rule::unique(User::class, 'username');
        if ($user !== null) {
            $uniqueUsernameRule->ignore($user->id);
        }

        $rules = ['string', $uniqueUsernameRule, 'min:3', 'max:20', new UsernameSyntaxRule()];
        if ($isRequired) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }
}
