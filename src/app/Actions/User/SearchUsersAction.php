<?php

namespace App\Actions\User;

use App\Domain\User\Data\UserSearchParams;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchUsersAction
{
    public function execute(UserSearchParams $params): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['roles:id,name', 'permissions:id,name', 'subscription']);

        $this->applyFilters($query, $params);
        $this->applySorting($query, $params);

        return $query
            ->paginate($params->perPage(), ['*'], 'page', $params->page())
            ->withQueryString();
    }

    protected function applyFilters(Builder $query, UserSearchParams $params): void
    {
        $filters = $params->filters();

        if ($search = $filters['search']) {
            $like = '%'.$search.'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("concat(first_name, ' ', last_name) like ?", [$like])
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone_number', 'like', $like);
            });
        }

        if ($email = $filters['email']) {
            $query->where('email', 'like', '%'.$email.'%');
        }

        if ($phone = $filters['phone_number']) {
            $query->where('phone_number', 'like', '%'.$phone.'%');
        }

        if ($role = $filters['role']) {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $role));
        }

        if ($plan = $filters['plan']) {
            $query->whereHas('subscription', fn (Builder $q) => $q->where('plan', $plan));
        }

        if (! is_null($filters['is_admin'])) {
            if ($filters['is_admin']) {
                $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'admin'));
            } else {
                $query->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'admin'));
            }
        }

        if (! is_null($filters['email_verified'])) {
            if ($filters['email_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($filters['created_from']) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if ($filters['created_to']) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }
    }

    protected function applySorting(Builder $query, UserSearchParams $params): void
    {
        [$column, $direction] = $params->sort();

        $query->orderBy($column, $direction);
    }
}
