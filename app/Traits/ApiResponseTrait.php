<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function successResponse(mixed $data = [], ?string $message = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'status' => true,
            'meta' => [
                'message' => $message ?? trans('api.response.success'),
                'errors'  => [],
            ],
            'data' => $data,
        ];

        return new JsonResponse($response, $statusCode);
    }

    public function createdResponse(mixed $data = [], ?string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message ?? trans('api.response.success'), Response::HTTP_CREATED);
    }

    public function errorResponse(mixed $errors = [], ?string $message = null, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $response = [
            'status' => false,
            'meta' => [
                'message' => $message ?? trans('api.response.error'),
                'errors'  => $errors,
            ],
            'data' => [],
        ];

        return new JsonResponse($response, $statusCode);
    }

    public function validationErrorResponse(mixed $errors, ?string $message = null): JsonResponse
    {
        return $this->errorResponse(
            errors: $errors,
            message: $message ?? trans('api.response.validation_error'),
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function unauthenticatedResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse(
            errors: ['auth' => [__('api.unauthenticated')]],
            message: $message ?? __('api.unauthenticated'),
            statusCode: Response::HTTP_UNAUTHORIZED
        );
    }

    public function successWithPagination(mixed $data, $paginator, ?string $message = null): JsonResponse
    {
        $response = [
            'status' => true,
            'meta' => [
                'message' => $message ?? trans('api.response.success'),
                'errors'  => [],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                ],
            ],
            'data' => $data,
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
