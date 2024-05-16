<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use ApiJsonResponseTrait;

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string'],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $perPage = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', -1);

        if ($sortOrder === 0) {
            $sortField = 'updated_at';
            $sortDirection = 'desc';
        } else {
            $sortField = $request->string('sort_field', 'updated_at');
            $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';
        }

        $posts = Post::orderBy($sortField, $sortDirection)->paginate($perPage);

        return $this->successJsonResponse([
            'records' => $posts->items(),
            'pagination' => [
                'total_records' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'total_pages' => $posts->lastPage(),
            ],
        ]);
    }

    public function getBySlug(string $slug): JsonResponse
    {
        return response()->json(Post::whereSlug($slug)->first());
    }
}
