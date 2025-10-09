<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Article\SearchArticlesAction;
use App\Domain\Article\Data\ArticleSearchParams;
use App\Domain\Article\Models\Article;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Article\SearchArticleRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;

class ArticleController extends ApiController
{
    public function __construct(private SearchArticlesAction $search) {}

    public function index(SearchArticleRequest $request)
    {
        $params = ArticleSearchParams::fromArray($request->validated(), 'api_public');
        $results = $this->search->execute($params);

        $resource = (new ArticleCollection($results))->additional([
            'filters' => array_filter($params->filters()),
        ]);

        return $this->respond($resource);
    }

    public function show(Article $article)
    {
        $article->load(['tags:id,slug,name', 'categories:id,slug,name', 'author:id,first_name,last_name,email']);
        return $this->respond(new ArticleResource($article));
    }
}
