<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Passport\SearchPassportsAction;
use App\Domain\Passport\Data\PassportSearchParams;
use App\Domain\Passport\Models\Passport;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Passport\SearchPassportRequest;
use App\Http\Resources\PassportCollection;
use App\Http\Resources\PassportResource;

class PassportController extends ApiController
{
    public function __construct(private SearchPassportsAction $searchPassports)
    {
    }

    public function index(SearchPassportRequest $request)
    {
        $params = PassportSearchParams::fromArray($request->validated(), 'api');
        $results = $this->searchPassports->execute($params);

        $resource = (new PassportCollection($results))->additional([
            'filters' => array_filter($params->filters()),
        ]);

        return $this->respond($resource);
    }

    public function show(string $passport)
    {
        $model = Passport::query()->findOrFail($passport);

        return $this->respond(new PassportResource($model));
    }
}
