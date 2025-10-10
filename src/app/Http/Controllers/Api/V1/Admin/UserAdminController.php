<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\User\SearchUsersAction;
use App\Domain\User\Data\UserSearchParams;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\SearchUserRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends ApiController
{
    public function __construct(private SearchUsersAction $searchUsers)
    {
    }

    public function index(SearchUserRequest $request)
    {
        $this->authorizeAdmin($request);

        $params = UserSearchParams::fromArray($request->validated());
        $results = $this->searchUsers->execute($params);

        $resource = (new UserCollection($results))->additional([
            'filters' => array_filter($params->filters(), static function ($value) {
                return $value !== null && $value !== '';
            }),
            'sort' => [
                'column' => $params->sort()[0],
                'direction' => $params->sort()[1],
            ],
        ]);

        return $this->respond($resource);
    }

    public function show(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $user->load(['roles:id,name', 'permissions:id,name', 'subscription']);

        return $this->respond(new UserResource($user));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        $role = $request->validated('role');

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        $user->load(['roles:id,name', 'permissions:id,name', 'subscription']);

        return $this->respond(new UserResource($user));
    }

    protected function authorizeAdmin(Request $request): void
    {
        if (! $request->user()?->hasRole('admin')) {
            abort(403, 'This action is only available to administrators.');
        }
    }
}
