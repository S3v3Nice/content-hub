<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use Auth;
use Illuminate\Http\JsonResponse;

class PostLikeController extends Controller
{
    use ApiJsonResponseTrait;

    public function like(int $postId): JsonResponse
    {
        $post = Post::find($postId);
        if ($post === null) {
            return $this->errorJsonResponse("Не найден материал с id $postId.");
        }

        $user = Auth::user();
        $isAlreadyLiked = PostLike::wherePostId($postId)->whereUserId($user->id)->exists();

        if (!$isAlreadyLiked) {
            $like = PostLike::make();
            $like->post()->associate($post);
            $like->user()->associate($user);
            $like->save();
        }

        return $this->successJsonResponse();
    }
    
    public function unlike(int $postId): JsonResponse
    {
        $post = Post::find($postId);
        if ($post === null) {
            return $this->errorJsonResponse("Не найден материал с id $postId.");
        }

        $user = Auth::user();
        $like = PostLike::wherePostId($postId)->whereUserId($user->id)->first();
        $like?->delete();

        return $this->successJsonResponse();
    }
}
