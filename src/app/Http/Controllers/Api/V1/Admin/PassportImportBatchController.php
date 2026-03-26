<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Passport\Models\PassportImportBatch;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PassportImportBatchResource;

class PassportImportBatchController extends ApiController
{
    public function show(PassportImportBatch $passportImportBatch)
    {
        return $this->respond(new PassportImportBatchResource($passportImportBatch));
    }
}
