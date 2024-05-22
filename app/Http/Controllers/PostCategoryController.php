<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Rules\ColumnExistsRule;
use App\Rules\SlugSyntaxRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostCategoryController extends Controller
{
    use ApiJsonResponseTrait;

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'with_posts' => ['boolean'],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(PostCategory::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $perPage = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', -1);

        if ($sortOrder === 0) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        } else {
            $sortField = $request->string('sort_field', 'created_at');
            $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';
        }

        $recordsQuery = PostCategory::orderBy($sortField, $sortDirection);
        if ($request->get('with_posts', false)) {
            $recordsQuery->with(['posts']);
        }

        $records = $recordsQuery->paginate($perPage);

        return $this->successJsonResponse([
            'records' => $records->items(),
            'pagination' => [
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'total_pages' => $records->lastPage(),
            ],
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', Rule::unique(PostCategory::class, 'slug'), new SlugSyntaxRule()],
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        PostCategory::create($request->only(['slug', 'name']));
        return $this->successJsonResponse();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = PostCategory::find($id);

        if ($category === null) {
            return $this->errorJsonResponse('Не найдено категории с id ' . $id);
        }

        $validator = Validator::make($request->all(), [
            'slug' => ['string', Rule::unique(PostCategory::class, 'slug')->ignore($category->id), new SlugSyntaxRule()],
            'name' => ['string'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $category->update($request->only(['slug', 'name']));
        return $this->successJsonResponse();
    }

    public function delete(int $id): JsonResponse
    {
        $category = PostCategory::find($id);

        if ($category === null) {
            return $this->errorJsonResponse('Не найдено категории с id ' . $id);
        }

        $category->delete();
        return $this->successJsonResponse();
    }

    public function deleteMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $ids = $request->get('ids');

        foreach ($ids as $id) {
            $category = PostCategory::find($id);
            $category?->delete();
        }

        return $this->successJsonResponse();
    }
}
