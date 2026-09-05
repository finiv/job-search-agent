<?php

namespace JobSearchAgent\Tool;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;
use JobSearchAgent\Adapter\Value\SearchCriteria;

class SearchVacanciesTool implements ToolInterface
{
    public function __construct(private readonly JobSiteAdapterInterface $adapter)
    {
    }

    public function name(): string
    {
        return 'search_vacancies';
    }

    public function description(): string
    {
        return 'Search job vacancies on the connected platform by a free-text query. '
            . 'Returns a JSON array of matches with id, title, url, budget, and a short snippet.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Free-text search query, e.g. "backend php laravel"'],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $input): string
    {
        $query = (string) ($input['query'] ?? '');
        $vacancies = $this->adapter->searchVacancies(new SearchCriteria($query));

        return json_encode(
            array_map(static fn ($vacancy) => $vacancy->toArray(), $vacancies),
            JSON_THROW_ON_ERROR
        );
    }
}
