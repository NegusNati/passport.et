<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiController extends Controller
{
    /**
     * Return a standardized JSON response payload.
     */
    protected function respond(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->response()->getData(true);
        }

        return response()->json($data, $status, $headers);
    }
}
