<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends ApiController
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->integer('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $search = trim((string) $request->query('search', ''));

        $query = User::query()
            ->with(['roles:id,name', 'permissions:id,name', 'subscription'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage)->withQueryString();

        return $this->respond(UserResource::collection($users));
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
