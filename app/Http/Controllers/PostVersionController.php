<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Models\PostVersion;
use App\Models\PostVersionAction;
use App\Models\PostVersionActionType;
use App\Models\PostVersionStatus;
use App\Models\User;
use App\Rules\ColumnExistsRule;
use App\Services\Dto\NewPostVersionDto;
use App\Services\Dto\PostVersionUpdateDto;
use App\Services\PostVersionService;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator as ValidatorInstance;

class PostVersionController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_COVER_SIZE_MB = 5;
    const MIN_COVER_WIDTH = 768;
    const MIN_COVER_HEIGHT = 432;

    public function __construct(private readonly PostVersionService $postVersionService)
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['integer', "min:0", "max:3"],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(PostVersion::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $status = PostVersionStatus::from($request->integer('status', PostVersionStatus::Pending->value));

        $defaultSortOrder = $status == PostVersionStatus::Pending ? 1 : -1;
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

        $postVersionsPaginator = PostVersion::whereStatus($status)
            ->orderBy($sortField, $sortDirection)
            ->with(['author', 'assignedModerator', 'actions.user'])
            ->paginate($perPage);

        $postVersions = $postVersionsPaginator->getCollection()->each(
            fn(PostVersion $item) => $item->makeVisible('assigned_moderator_id')
        )->all();

        return $this->successJsonResponse([
            'records' => $postVersions,
            'pagination' => [
                'total_records' => $postVersionsPaginator->total(),
                'current_page' => $postVersionsPaginator->currentPage(),
                'total_pages' => $postVersionsPaginator->lastPage(),
            ],
        ]);
    }

    public function getById(int $id): JsonResponse
    {
        $user = Auth::user();

        $query = PostVersion::with(['author', 'post', 'category', 'actions.user'])->with([
                'actions' => function (HasMany $query) use ($user) {
                    if (!$user->is_moderator) {
                        $query->whereNot('type', PostVersionActionType::AssignModerator);
                    }
                }
            ]
        );
        if ($user->is_moderator) {
            $query = $query->with('assignedModerator');
        }

        /** @var PostVersion|null $postVersion */
        $postVersion = $query->find($id);
        if ($postVersion === null || (!$user->is_moderator && $postVersion->author_id !== $user->id)) {
            return response()->json(null);
        }

        if ($user->is_moderator) {
            $postVersion->makeVisible('assigned_moderator_id');
            $postVersion->actions->each(function (PostVersionAction $action) {
                if ($action->type === PostVersionActionType::AssignModerator) {
                    $details = $action->details;
                    $details['moderator'] = User::find($details['moderator_id']);
                    $action->details = $details;
                }
            });
        } else {
            $postVersion->actions->each(function (PostVersionAction $action) {
                if ($action->type !== PostVersionActionType::Submit) {
                    $action->makeHidden('user_id');
                    $action->makeHidden('user');
                }
            });
        }

        return response()->json($postVersion);
    }

    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['integer', "min:0", "max:3"],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(PostVersion::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();

        if ($user->id !== $userId) {
            if (!$user->is_moderator) {
                return $this->errorJsonResponse("Вам недоступны версии материалов пользователя с id $userId.");
            }

            $user = User::find($userId);
            if ($user === null) {
                return $this->errorJsonResponse("Не существует пользователя с id $userId.");
            }
        }

        $defaultSortOrder = -1;
        $defaultSortField = 'updated_at';

        $status = PostVersionStatus::from($request->integer('status', PostVersionStatus::Pending->value));
        $perPage = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        if ($sortOrder === 0) {
            $sortField = $defaultSortField;
            $sortOrder = $defaultSortOrder;
        } else {
            $sortField = $request->string('sort_field', $defaultSortField);
        }
        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        $query = PostVersion
            ::whereAuthorId($userId)
            ->whereStatus($status)
            ->orderBy($sortField, $sortDirection)
            ->with(['author', 'actions.user'])->with([
                    'actions' => function (HasMany $query) use ($user) {
                        if (!$user->is_moderator) {
                            $query->whereNot('type', PostVersionActionType::AssignModerator);
                        }
                    }
                ]
            );

        if ($user->is_moderator) {
            $query = $query->with('assignedModerator');
        }

        $postVersionsPaginator = $query->paginate($perPage);
        $postVersions = $postVersionsPaginator
            ->getCollection()
            ->each(function (PostVersion $postVersion) use ($user) {
                if ($user->is_moderator) {
                    $postVersion->makeVisible('assigned_moderator_id');
                } else {
                    $postVersion->actions->each(function (PostVersionAction $action) {
                        if ($action->type !== PostVersionActionType::Submit) {
                            $action->makeHidden('user_id');
                            $action->makeHidden('user');
                        }
                    });
                }
            })
            ->all();

        return $this->successJsonResponse([
            'records' => $postVersions,
            'pagination' => [
                'total_records' => $postVersionsPaginator->total(),
                'current_page' => $postVersionsPaginator->currentPage(),
                'total_pages' => $postVersionsPaginator->lastPage(),
            ],
        ]);
    }

    public function assignModerator(Request $request, int $versionId): JsonResponse
    {
        $postVersion = PostVersion::find($versionId);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $versionId.");
        }

        $validator = Validator::make($request->all(), [
            'moderator_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        try {
            $this->postVersionService->assignModerator($postVersion, $request->integer('moderator_id'));
        } catch (Exception $e) {
            return $this->errorJsonResponse($e->getMessage());
        }

        return $this->successJsonResponse();
    }

    public function createDraft(Request $request): JsonResponse
    {
        $validator = $this->getNewPostVersionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = $this->postVersionService->createDraft(
            new NewPostVersionDto(
                Auth::user(),
                PostCategory::find($request->integer('category_id')),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
            )
        );

        return $this->successJsonResponse([
            'id' => $postVersion->id
        ]);
    }

    public function submitNew(Request $request): JsonResponse
    {
        $validator = $this->getNewPostVersionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = $this->postVersionService->submitNew(
            new NewPostVersionDto(
                Auth::user(),
                PostCategory::find($request->integer('category_id')),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
            )
        );

        return $this->successJsonResponse([
            'id' => $postVersion->id
        ]);
    }

    public function updateDraft(Request $request, int $id): JsonResponse
    {
        $validator = $this->getPostVersionUpdateValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = PostVersion::find($id);
        if ($postVersion === null
            || $postVersion->status !== PostVersionStatus::Draft
            || $postVersion->author_id !== Auth::user()->id
        ) {
            return $this->errorJsonResponse("У вас нет черновика с id $id.");
        }

        $this->postVersionService->updateDraft(
            $postVersion,
            new PostVersionUpdateDto(
                $request->integer('category_id', null),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
            )
        );

        return $this->successJsonResponse();
    }


    public function submit(Request $request, int $id): JsonResponse
    {
        $validator = $this->getPostVersionUpdateValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = PostVersion::find($id);
        if ($postVersion === null
            || $postVersion->status !== PostVersionStatus::Draft
            || $postVersion->author_id !== Auth::user()->id
        ) {
            return $this->errorJsonResponse("У вас нет черновика с id $id.");
        }

        $this->postVersionService->submit(
            $postVersion,
            new PostVersionUpdateDto(
                $request->integer('category_id', null),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
            )
        );

        return $this->successJsonResponse();
    }

    public function requestChanges(Request $request, int $id): JsonResponse
    {
        $validator = $this->getPostVersionUpdateValidator($request->all(), [
            'details' => ['required', 'array'],
            'details.message' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }
        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя вернуть на доработку из-за её статуса.");
        }

        $this->postVersionService->requestChanges(
            $postVersion,
            new PostVersionUpdateDto(
                $request->integer('category_id', null),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
                $request->input('details'),
            )
        );

        return $this->successJsonResponse();
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $validator = $this->getPostVersionUpdateValidator($request->all(), [
            'slug' => ['string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }
        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя принять из-за её статуса.");
        }

        $this->postVersionService->accept(
            $postVersion,
            new PostVersionUpdateDto(
                $request->integer('category_id', null),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
                null,
                $request->string('slug')
            )
        );

        return $this->successJsonResponse();
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validator = $this->getPostVersionUpdateValidator($request->all(), [
            'details' => ['required', 'array'],
            'details.reason' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }
        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя отклонить из-за её статуса.");
        }

        $this->postVersionService->reject(
            $postVersion,
            new PostVersionUpdateDto(
                $request->integer('category_id', null),
                $request->string('title'),
                $request->file('cover_file'),
                $request->string('description'),
                $request->string('content'),
                $request->input('details'),
            )
        );

        return $this->successJsonResponse();
    }

    private function getNewPostVersionValidator(array $data, array $additionalRules = []): ValidatorInstance
    {
        return Validator::make($data, array_merge(
            [
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
                'title' => ['required', 'string', 'max:150'],
                'description' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string', 'max:65535'],
            ],
            $additionalRules
        ));
    }

    private function getPostVersionUpdateValidator(array $data, array $additionalRules = []): ValidatorInstance
    {
        return Validator::make($data, array_merge(
            [
                'category_id' => ['integer', Rule::exists(PostCategory::class, 'id')],
                'cover_file' => [
                    File::types(['jpeg', 'jpg', 'png']),
                    File::image()
                        ->max(self::MAX_COVER_SIZE_MB * 1024)
                        ->dimensions(
                            Rule::dimensions()
                                ->minWidth(self::MIN_COVER_WIDTH)
                                ->minHeight(self::MIN_COVER_HEIGHT)
                        ),
                ],
                'title' => ['string', 'max:150'],
                'description' => ['string', 'max:255'],
                'content' => ['string', 'max:65535'],
            ],
            $additionalRules
        ));
    }
}
