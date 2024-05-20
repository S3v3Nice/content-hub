<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Rules\ColumnExistsRule;
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
            'sort_field' => ['string', new ColumnExistsRule(Post::getModel()->getTable())],
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

    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(Post::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $defaultSortOrder = -1;
        $defaultSortField = 'updated_at';

        $perPage = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        if ($sortOrder === 0) {
            $sortField = $defaultSortField;
            $sortOrder = $defaultSortOrder;
        } else {
            $sortField = $request->string('sort_field', $defaultSortField);
        }
        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        $posts = Post::whereHas('versions', function ($query) use ($userId) {
            $query->where('author_id', $userId)->limit(1);
        })->orderBy($sortField, $sortDirection)->paginate($perPage);

        return $this->successJsonResponse([
            'records' => $posts->items(),
            'pagination' => [
                'total_records' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'total_pages' => $posts->lastPage(),
            ],
        ]);
    }
}
