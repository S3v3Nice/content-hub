<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Models\PostVersion;
use App\Models\PostVersionAction;
use App\Models\PostVersionActionType;
use App\Models\PostVersionStatus;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class PostVersionController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_COVER_SIZE_MB = 5;
    const MIN_COVER_WIDTH = 768;
    const MIN_COVER_HEIGHT = 432;

    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', Rule::exists(PostCategory::class, 'id')],
            'cover_file' => [
                'required',
                File::types(['jpeg', 'jpg', 'png']),
                File::image()
                    ->max(self::MAX_COVER_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(self::MIN_COVER_WIDTH)
                            ->minHeight(self::MIN_COVER_HEIGHT)
                    ),
            ],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $image = $request->file('cover_file');
        $imagePath = $image->storeAs('public/images', $image->hashName());
        $publicImagePath = substr_replace($imagePath, 'storage/', 0, strlen('public/'));

        $user = Auth::user();

        $postVersion = PostVersion::make($request->only(['title', 'description', 'content']));
        $postVersion->cover = $publicImagePath;
        $postVersion->status = PostVersionStatus::Pending;
        $postVersion->author()->associate($user);
        $postVersion->category()->associate(PostCategory::find($request->get('category_id')));
        $postVersion->save();

        $submitAction = PostVersionAction::make();
        $submitAction->action = PostVersionActionType::Submit;
        $submitAction->version()->associate($postVersion);
        $submitAction->user()->associate($user);
        $submitAction->save();

        return $this->successJsonResponse();
    }
}
