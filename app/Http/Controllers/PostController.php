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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use ApiJsonResponseTrait;

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_slug' => ['string', Rule::exists(PostCategory::class, 'slug')],
            'term' => ['string', 'nullable'],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_type' => ['integer', 'min:0', 'max:1'],
            'period' => ['integer', 'min:0', 'max:4'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $perPage = $request->integer('per_page', 10);
        $sortType = PostSortType::from($request->integer('sort_type'));
        $period = PostLoadPeriod::from($request->integer('period'));

        $postsQuery = match ($sortType) {
            PostSortType::Latest => Post::orderBy('updated_at', 'desc'),
            PostSortType::Popular => Post::withCount(['likes', 'comments', 'views'])
                ->orderByRaw('(likes_count * 2) + (comments_count * 3) + views_count DESC')
                ->orderBy('updated_at', 'desc'),
        };

        $startDate = match ($period) {
            PostLoadPeriod::Day => Carbon::now()->subDay(),
            PostLoadPeriod::Week => Carbon::now()->subWeek(),
            PostLoadPeriod::Month => Carbon::now()->subMonth(),
            PostLoadPeriod::Year => Carbon::now()->subYear(),
            PostLoadPeriod::AllTime => null,
        };

        if ($startDate !== null) {
            $postsQuery->where('updated_at', '>=', $startDate);
        }

        $categorySlug = $request->string('category_slug');
        $searchTerm = $request->string('term');

        if ($searchTerm->isNotEmpty() || $categorySlug->isNotEmpty()) {
            $postsQuery->whereHas('versions', function (Builder $query) use ($searchTerm, $categorySlug) {
                $query->whereIn('id', function (QueryBuilder $subQuery) {
                    $subQuery->selectRaw('MAX(id)')
                        ->from('post_versions')
                        ->where('status', PostVersionStatus::Accepted)
                        ->groupBy('post_id');
                });
                if ($categorySlug->isNotEmpty()) {
                    $query->whereHas('category', function (Builder $categoryQuery) use ($categorySlug) {
                        $categoryQuery->where('slug', '=', $categorySlug);
                    });
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
