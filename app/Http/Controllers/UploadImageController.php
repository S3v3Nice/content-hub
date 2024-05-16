<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UploadImageController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_IMAGE_SIZE_MB = 5;

    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                File::types(['jpeg', 'jpg', 'png']),
                File::image()
                    ->max(self::MAX_IMAGE_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(100)
                            ->minHeight(100)
                    ),
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $image = $request->file('image');
        $imagePath = $image->store('images', ['disk' => 'public']);

        return $this->successJsonResponse([
            'image_url' => url(Storage::url($imagePath))
        ]);
    }
}
