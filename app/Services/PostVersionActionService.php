<?php

namespace App\Services;

use App\Models\PostVersionAction;
use App\Services\Dto\PostVersionActionDto;
use Carbon\Carbon;

class PostVersionActionService
{
    public function create(PostVersionActionDto $dto, ?Carbon $dateTime = null): PostVersionAction
    {
        $action = PostVersionAction::make();
        $action->version()->associate($dto->version);
        $action->user()->associate($dto->user);
        $action->type = $dto->type;
        $action->details = $dto->details;
        if ($dateTime !== null) {
            $action->created_at = $dateTime;
            $action->updated_at = $dateTime;
        }
        $action->save();

        return $action;
    }
}
