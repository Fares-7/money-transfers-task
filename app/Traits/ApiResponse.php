<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ], $code);
    }

    /**
     * Return a failure JSON response (e.g. validation or business rule fail).
     *
     * @param  mixed  $data
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failResponse($data, int $code = 422): JsonResponse
    {
        return response()->json([
            'status' => 'fail',
            'data'   => $data,
        ], $code);
    }

    /**
     * Return an error JSON response (e.g. server error).
     *
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $code);
    }
}
