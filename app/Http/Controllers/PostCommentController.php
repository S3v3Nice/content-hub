<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Rules\ColumnExistsRule;
use App\Rules\HtmlMinSymbolCountRule;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostCommentController extends Controller
{
    use ApiJsonResponseTrait;

    public function getByPostId(Request $request, int $postId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sort_field' => ['string', new ColumnExistsRule(Post::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $defaultSortOrder = -1;
        $defaultSortField = 'created_at';

        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        if ($sortOrder === 0) {
            $sortField = $defaultSortField;
            $sortOrder = $defaultSortOrder;
        } else {
            $sortField = $request->string('sort_field', $defaultSortField);
        }
        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        $postComments = PostComment::wherePostId($postId)->with(['user', 'parentComment.user'])->orderBy($sortField, $sortDirection)->get();

        return $this->successJsonResponse([
            'records' => $postComments
        ]);
    }

    public function submit(Request $request, int $postId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:65535', new HtmlMinSymbolCountRule(3)],
            'parent_comment_id' => ['integer', Rule::exists(PostComment::class, 'id')],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $post = Post::find($postId);
        if ($post === null) {
            return $this->errorJsonResponse("Материал с id $postId не найден.");
        }

        $parentComment = $request->has('parent_comment_id')
            ? PostComment::find($request->integer('parent_comment_id'))
            : null;

        $user = Auth::user();

        $comment = PostComment::make();
        $comment->content = $request->get('content');
        $comment->post()->associate($post);
        $comment->user()->associate($user);
        if ($parentComment !== null) {
            $comment->parentComment()->associate($parentComment);
        }

        $comment->save();

        return $this->successJsonResponse([
            'id' => $comment->id
        ]);
    }

    public function remove(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $user = Auth::user();
        if ($user->id !== $comment->user_id && !$user->is_moderator) {
            return $this->errorJsonResponse("У вас нет прав для удаления данного комментария.");
        }
        if (!$user->is_moderator && Carbon::now()->diffInMinutes($comment->created_at) >= 30) {
            return $this->errorJsonResponse("Комментарий нельзя удалить, так как он опубликован более 30 мин. назад.");
        }

        $comment->delete();
        return $this->successJsonResponse();
    }

    public function edit(Request $request, int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:65535', new HtmlMinSymbolCountRule(3)],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        if ($user->id !== $comment->user_id) {
            return $this->errorJsonResponse("У вас нет прав для изменения данного комментария.");
        }
        if (Carbon::now()->diffInMinutes($comment->created_at) >= 30) {
            return $this->errorJsonResponse("Комментарий нельзя изменить, так как он опубликован более 30 мин. назад.");
        }

        $comment->content = $request->string('content');
        $comment->save();

        return $this->successJsonResponse();
    }
}
