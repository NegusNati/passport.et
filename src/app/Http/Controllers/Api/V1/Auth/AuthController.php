<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Http\Requests\Auth\ApiRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function register(ApiRegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        $user->tokens()->where('name', $user->id.'-spa')->delete();

        $token = $user->createToken(
            $user->id.'-spa',
            ['api']
        )->plainTextToken;

        return $this->respond([
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(ApiLoginRequest $request)
    {
        $credentials = [
            'phone_number' => $request->input('phone_number'),
            'password' => $request->input('password'),
        ];

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
