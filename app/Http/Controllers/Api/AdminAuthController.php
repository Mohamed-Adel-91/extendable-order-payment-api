<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Resources\AdminAuthTokenResource;
use App\Http\Resources\AdminResource;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthController extends Controller
{
    public function __construct(private readonly AdminAuthService $adminAuthService)
    {
    }

    public function login(AdminLoginRequest $request): JsonResponse
    {
        $payload = $this->adminAuthService->login($request->validated());

        if ($payload === null) {
            return $this->errorResponse(
                errors: ['auth' => [trans('api.auth.invalid_credentials')]],
                message: trans('api.auth.invalid_credentials'),
                statusCode: Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->successResponse(
            data: (new AdminAuthTokenResource($payload))->resolve(),
            message: trans('api.auth.login_success')
        );
    }

    public function logout(): JsonResponse
    {
        $this->adminAuthService->logout();

        return $this->successResponse(message: trans('api.auth.logout_success'));
    }

    public function me(): JsonResponse
    {
        $payload = $this->adminAuthService->me();

        if ($payload === null) {
            return $this->unauthenticatedResponse(trans('api.auth.unauthorized'));
        }

        return $this->successResponse(
            data: ['admin' => (new AdminResource($payload['admin']))->resolve()],
            message: trans('api.auth.profile_success')
        );
    }
}
