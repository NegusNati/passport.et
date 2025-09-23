<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;

class LocationController extends ApiController
{
    /**
     * Placeholder endpoint for location metadata.
     */
    public function index()
    {
        return $this->respond([
            'status' => 'not_implemented',
            'message' => 'Location API will be implemented in Phase 3.',
        ], 501);
    }
}
