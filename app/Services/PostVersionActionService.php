<?php

namespace App\Services;

use App\Models\PostVersionAction;
use App\Services\Dto\PostVersionActionDto;

class PostVersionActionService
{
    public function create(PostVersionActionDto $dto): void
    {
        $action = PostVersionAction::make();
        $action->version()->associate($dto->version);
        $action->user()->associate($dto->user);
        $action->type = $dto->type;
        $action->details = $dto->details ? json_encode($dto->details) : '{}';
        $action->save();
    }
}
