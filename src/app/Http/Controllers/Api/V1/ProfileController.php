<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    public function show(Request $request)
    {
        return $this->respond(new UserResource($request->user()));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $this->respond(new UserResource($user));
    }
}
