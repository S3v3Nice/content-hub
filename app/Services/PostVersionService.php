<?php

namespace App\Services;

use App\Models\PostVersion;
use App\Models\PostVersionActionType;
use App\Models\PostVersionStatus;
use App\Services\Dto\NewPostVersionDto;
use App\Services\Dto\PostVersionActionDto;
use App\Services\Dto\PostVersionUpdateDto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostVersionService
{
    public function __construct(
        private readonly PostVersionActionService $postVersionActionService,
        private readonly PostService              $postService
    )
    {
    }

    public function createDraft(NewPostVersionDto $dto): PostVersion
    {
        return $this->createPostVersion($dto, PostVersionStatus::Draft);
    }

    public function submitNew(NewPostVersionDto $dto): PostVersion
    {
        $postVersion = $this->createPostVersion($dto, PostVersionStatus::Pending);

        $this->postVersionActionService->create(new PostVersionActionDto(
            $postVersion,
            Auth::user(),
            PostVersionActionType::Submit
        ));

        return $postVersion;
    }

    public function requestChanges(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Draft);

        $this->postVersionActionService->create(new PostVersionActionDto(
            $postVersion,
            Auth::user(),
            PostVersionActionType::RequestChanges,
            $dto->actionDetails
        ));
    }

    public function accept(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        if ($postVersion->post_id === null) {
            $post = $this->postService->create($dto->slug ?? Str::slug($dto->title));
            $postVersion->post()->associate($post);
        }

        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Accepted);
        $postVersion->post->touch();

        $this->postVersionActionService->create(new PostVersionActionDto(
            $postVersion,
            Auth::user(),
            PostVersionActionType::Accept
        ));
    }

    public function reject(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Rejected);

        $this->postVersionActionService->create(new PostVersionActionDto(
            $postVersion,
            Auth::user(),
            PostVersionActionType::Reject,
            $dto->actionDetails
        ));
    }

    private function createPostVersion(NewPostVersionDto $dto, PostVersionStatus $status): PostVersion
    {
        $postVersion = PostVersion::make();
        $postVersion->author()->associate($dto->author);
        $postVersion->category()->associate($dto->category);
        $postVersion->title = $dto->title;
        $postVersion->cover = $this->saveCover($dto->coverFile);
        $postVersion->description = $dto->description;
        $postVersion->content = $dto->content;
        $postVersion->status = $status;
        $postVersion->save();

        return $postVersion;
    }

    private function updatePostVersion(PostVersion $postVersion, PostVersionUpdateDto $dto, PostVersionStatus $status): void
    {
        if ($dto->category !== null) {
            $postVersion->category()->associate($dto->category);
        }
        if ($dto->title !== null) {
            $postVersion->title = $dto->title;
        }
        if ($dto->coverFile !== null) {
            $postVersion->cover = $this->saveCover($dto->coverFile);
        }
        if ($dto->description !== null) {
            $postVersion->description = $dto->description;
        }
        if ($dto->content !== null) {
            $postVersion->content = $dto->content;
        }

        $postVersion->status = $status;
        $postVersion->save();
    }

    private function saveCover(UploadedFile $coverFile): string
    {
        $coverPath = $coverFile->store('images', ['disk' => 'public']);
        return str_replace('public/', '', $coverPath);
    }
}
