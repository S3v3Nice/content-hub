<?php

namespace App\Services\Dto;

use App\Models\PostCategory;
use Illuminate\Http\UploadedFile;

class PostVersionUpdateDto
{
    public function __construct(
        public readonly ?PostCategory $category = null,
        public readonly ?string       $title = null,
        public readonly ?UploadedFile $coverFile = null,
        public readonly ?string       $description = null,
        public readonly ?string       $content = null,
        public readonly ?array        $actionDetails = null,
        public readonly ?string       $slug = null,
    )
    {
    }
}
