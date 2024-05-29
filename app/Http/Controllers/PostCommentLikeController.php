<?php

namespace App\Http\Controllers;

use App\Models\PostComment;
use App\Models\PostCommentLike;
use Auth;
use Illuminate\Http\JsonResponse;

class PostCommentLikeController extends Controller
{
    use ApiJsonResponseTrait;

    public function like(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Не найден комментарий с id $commentId.");
        }

        $user = Auth::user();
        $isAlreadyLiked = PostCommentLike::whereCommentId($commentId)->whereUserId($user->id)->exists();

        if (!$isAlreadyLiked) {
            $like = PostCommentLike::make();
            $like->comment()->associate($comment);
            $like->user()->associate($user);
            $like->save();
        }

        return $this->successJsonResponse();
    }

    public function unlike(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Не найден материал с id $commentId.");
        }

        $user = Auth::user();
        $like = PostCommentLike::whereCommentId($commentId)->whereUserId($user->id)->first();
        $like?->delete();

        return $this->successJsonResponse();
    }
}
