<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;

class PostService
{
    public function create(string $slug, ?Carbon $dateTime = null): Post
    {
        $post = Post::make();
        $post->slug = $slug;
        if ($dateTime !== null) {
            $post->created_at = $dateTime;
            $post->updated_at = $dateTime;
        }
        $post->save();

        return $post;
    }
}
