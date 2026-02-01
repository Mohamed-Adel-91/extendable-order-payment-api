<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{

    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $payload = $this->authService->register($request->validated());

            return $this->createdResponse(
                data: (new AuthTokenResource($payload))->resolve(),
                message: trans('api.auth.register_success')
            );
        } catch (Throwable $e) {
            return $this->validationErrorResponse(
                errors: ['register' => [trans('api.auth.register_failed')]],
                message: trans('api.auth.register_failed')
            );
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login($request->validated());

        if ($payload === null) {
            return $this->errorResponse(
                errors: ['auth' => [trans('api.auth.invalid_credentials')]],
                message: trans('api.auth.invalid_credentials'),
                statusCode: Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->successResponse(
            data: (new AuthTokenResource($payload))->resolve(),
            message: trans('api.auth.login_success')
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse(
            message: trans('api.auth.logout_success')
        );
    }

    public function me(): JsonResponse
    {
        $payload = $this->authService->me();

        if ($payload === null) {
            return $this->unauthenticatedResponse(
                trans('api.auth.unauthorized')
            );
        }

        return $this->successResponse(
            data: ['user' => (new UserResource($payload['user']))->resolve()],
            message: trans('api.auth.profile_success')
        );
    }
}
