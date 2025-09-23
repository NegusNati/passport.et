<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends ApiController
{
    public function login(ApiLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->respond([
                'status' => 'error',
                'code' => 'invalid_credentials',
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->tokens()->where('name', $user->id.'-spa')->delete();

        $token = $user->createToken(
            $user->id.'-spa',
            ['api']
        )->plainTextToken;

        return $this->respond([
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        return $this->respond(new UserResource($request->user()));
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->respond(null, 204);
    }
}
