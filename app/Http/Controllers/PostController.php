<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    use ApiJsonResponseTrait;

    public function getBySlug(string $slug): JsonResponse
    {
        return response()->json(Post::whereSlug($slug)->first());
    }
}
