<?php

namespace App\Services\Dto;

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class NewPostVersionDto
{
    public function __construct(
        public readonly User         $author,
        public readonly PostCategory $category,
        public readonly string       $title,
        public readonly UploadedFile $coverFile,
        public readonly string       $description,
        public readonly string       $content,
    )
    {
    }
}
