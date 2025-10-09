<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminAbilityController extends ApiController
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            abort(403, 'This action is only available to administrators.');
        }

        $abilities = [
            'viewPulse' => Gate::forUser($user)->allows('viewPulse'),
            'viewHorizon' => Gate::forUser($user)->allows('viewHorizon'),
            'manageArticles' => Gate::forUser($user)->allows('manage-articles'),
        ];

        return $this->respond([
            'data' => [
                'abilities' => $abilities,
            ],
        ]);
    }
}
