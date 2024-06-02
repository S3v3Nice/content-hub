<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ColumnExistsRule;
use App\Rules\NotVerifiedEmailRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiJsonResponseTrait;
    use UsernameValidationRulesTrait;
    use PasswordValidationRulesTrait;

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'roles.*' => ['integer'],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(User::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $sortOrder = $request->integer('sort_order', -1);

        if ($sortOrder === 0) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        } else {
            $sortField = $request->string('sort_field', 'created_at');
            $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';
        }

        $query = User::orderBy($sortField, $sortDirection);
        if ($request->has('roles')) {
            $query->whereIn('role', $request->input('roles'));
        }

        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->integer('per_page', 10);
            $users = $query->paginate($perPage);

            return $this->successJsonResponse([
                'records' => $users->items(),
                'pagination' => [
                    'total_records' => $users->total(),
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                ],
            ]);
        }

        return $this->successJsonResponse([
            'records' => $query->get(),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => $this->getUsernameRules(),
            'email' => ['required', 'email', (new NotVerifiedEmailRule())],
            'password' => $this->getPasswordRules(false),
            'role' => ['required', 'integer', 'min:0', 'max:2'],
            'first_name' => ['string', 'nullable'],
            'last_name' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = User::make($request->only(['username', 'email', 'password', 'role', 'first_name', 'last_name']));

        $user->save();

        return $this->successJsonResponse();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return $this->errorJsonResponse('Не найдено пользователя с id ' . $id);
        }

        $validator = Validator::make($request->all(), [
            'username' => $this->getUsernameRules(false, $user),
            'email' => ['email', (new NotVerifiedEmailRule())->ignore($user->id)],
            'role' => ['integer', 'min:0', 'max:2'],
            'first_name' => ['string', 'nullable'],
            'last_name' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $oldEmail = $user->email;

        $user->fill($request->only(['username', 'email', 'role', 'first_name', 'last_name']));
        if ($user->email !== $oldEmail) {
            $user->email_verified_at = null;
        }

        $user->save();
        return $this->successJsonResponse();
    }

    public function delete(int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return $this->errorJsonResponse('Не найдено пользователя с id ' . $id);
        }

        $user->delete();
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
            $user = User::find($id);
            $user?->delete();
        }

        return $this->successJsonResponse();
    }
}
