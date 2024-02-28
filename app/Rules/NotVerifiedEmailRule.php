<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class NotVerifiedEmailRule implements ValidationRule
{
    private mixed $ignoreId = null;
    private string $ignoreIdColumn = 'id';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $verified = User::where('email', $value)->whereNotNull('email_verified_at');

        if ($this->ignoreId !== null) {
            $verified = $verified->where($this->ignoreIdColumn, '!=', $this->ignoreId);
        }

        if ($verified->exists()) {
            $fail('Данный E-mail уже используется и подтвержден на другом аккаунте.');
        }
    }

    public function ignore($id, $idColumn = null): self
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignoreId = $id;
        $this->ignoreIdColumn = $idColumn ?? $this->ignoreIdColumn;

        return $this;
    }

    public function ignoreModel($model, $idColumn = null): self
    {
        $this->ignoreIdColumn = $idColumn ?? $model->getKeyName();
        $this->ignoreId = $model->{$this->ignoreIdColumn};

        return $this;
    }
}
