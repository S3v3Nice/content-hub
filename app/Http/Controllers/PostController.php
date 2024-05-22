<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostVersionStatus;
use App\Models\PostView;
use App\Rules\ColumnExistsRule;
use Illuminate\Database\Eloquent\Builder as Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use ApiJsonResponseTrait;

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['integer', Rule::exists(PostCategory::class, 'id')],
            'term' => ['string', 'nullable'],
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

        $categoryId = $request->integer('category_id', -1);
        $searchTerm = $request->string('term');

        $postsQuery = Post::orderBy($sortField, $sortDirection);
        if ($searchTerm->isNotEmpty() || $categoryId !== -1) {
            $postsQuery->whereHas('versions', function (Builder $query) use ($searchTerm, $categoryId) {
                $query->whereIn('id', function (QueryBuilder $subQuery) {
                    $subQuery->selectRaw('MAX(id)')
                        ->from('post_versions')
                        ->where('status', PostVersionStatus::Accepted)
                        ->groupBy('post_id');
                });
                if ($categoryId !== -1) {
                    $query->where('category_id', '=', $categoryId);
                }
                if ($searchTerm->isNotEmpty()) {
                    $query->where(function ($query) use ($searchTerm) {
                        $query->where('title', 'like', '%' . $searchTerm . '%')
                            ->orWhere('description', 'like', '%' . $searchTerm . '%');
                    });
                }
            });
        }

        $posts = $postsQuery->paginate($perPage);
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
        $post = Post::whereSlug($slug)->first();
        if ($post !== null) {
            $postView = PostView::wherePostId($post->id)->first();
            if ($postView === null) {
                $newPostView = PostView::make();
                $newPostView->ip = request()->ip();
                $newPostView->post()->associate($post);
                $newPostView->save();
            }
        }

        return response()->json($post);
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
