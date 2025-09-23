<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Passport\SearchPassportRequest;

class PassportController extends ApiController
{
    /**
     * Placeholder index endpoint until the domain layer is extracted.
     */
    public function index(SearchPassportRequest $request)
    {
        return $this->respond([
            'status' => 'not_implemented',
            'message' => 'Passport search API will be available once Phase 2 is complete.',
            'filters' => $request->validated(),
        ], 501);
    }

    /**
     * Placeholder show endpoint for individual passport details.
     */
    public function show(string $passport)
    {
        return $this->respond([
            'status' => 'not_implemented',
            'message' => 'Passport detail API will be available once Phase 3 is complete.',
            'id' => $passport,
        ], 501);
    }
}
