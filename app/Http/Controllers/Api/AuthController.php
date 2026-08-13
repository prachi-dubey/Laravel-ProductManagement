<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\Auth\AuthResource;
use App\Http\Resources\Api\Auth\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /** @var AuthService */
    private $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->auth->register($request->validated());

        return $this->success(
            __('messages.auth.registered'),
            new AuthResource($payload),
            Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->auth->login($request->validated());

        return $this->success(
            __('messages.auth.logged_in'),
            new AuthResource($payload)
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return $this->success(__('messages.auth.logged_out'));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->auth->me($request->user());

        return $this->success(
            __('messages.auth.me_retrieved'),
            new UserResource($user)
        );
    }
}
