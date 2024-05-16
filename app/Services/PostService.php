<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    public function create(string $slug): Post
    {
        $post = Post::make();
        $post->slug = $slug;
        $post->save();

        return $post;
    }
}
