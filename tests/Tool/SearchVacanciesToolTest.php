<?php

namespace Tests\Tool;

use JobSearchAgent\Adapter\Value\Vacancy;
use JobSearchAgent\Tool\SearchVacanciesTool;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeJobSiteAdapter;

class SearchVacanciesToolTest extends TestCase
{
    public function test_execute_returns_json_encoded_vacancies(): void
    {
        $adapter = new FakeJobSiteAdapter(vacancies: [
            new Vacancy(id: 'job1', title: 'Senior PHP Dev', url: 'https://example.com/job1'),
        ]);

        $tool = new SearchVacanciesTool($adapter);
        $output = $tool->execute(['query' => 'php']);

        $decoded = json_decode($output, true);
        $this->assertSame('job1', $decoded[0]['id']);
        $this->assertSame('Senior PHP Dev', $decoded[0]['title']);
    }

    public function test_schema_requires_query(): void
    {
        $tool = new SearchVacanciesTool(new FakeJobSiteAdapter());

        $this->assertSame(['query'], $tool->inputSchema()['required']);
    }
}
