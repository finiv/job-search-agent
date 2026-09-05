<?php

namespace JobSearchAgent\Tool;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;

class GetVacancyDetailsTool implements ToolInterface
{
    public function __construct(private readonly JobSiteAdapterInterface $adapter)
    {
    }

    public function name(): string
    {
        return 'get_vacancy_details';
    }

    public function description(): string
    {
        return 'Get the full description, budget, and required skills for one vacancy by id. '
            . 'Use this before shortlisting a vacancy to judge real fit, not just the search snippet.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'vacancy_id' => ['type' => 'string', 'description' => 'The vacancy id returned by search_vacancies'],
            ],
            'required' => ['vacancy_id'],
        ];
    }

    public function execute(array $input): string
    {
        $id = (string) ($input['vacancy_id'] ?? '');

        try {
            $detail = $this->adapter->getVacancyDetails($id);
        } catch (\Throwable $e) {
            return "Error: unable to fetch vacancy '{$id}': " . $e->getMessage();
        }

        return json_encode($detail->toArray(), JSON_THROW_ON_ERROR);
    }
}
