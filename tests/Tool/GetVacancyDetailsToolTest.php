<?php

namespace Tests\Tool;

use JobSearchAgent\Adapter\Value\VacancyDetail;
use JobSearchAgent\Tool\GetVacancyDetailsTool;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeJobSiteAdapter;

class GetVacancyDetailsToolTest extends TestCase
{
    public function test_execute_returns_json_encoded_detail(): void
    {
        $adapter = new FakeJobSiteAdapter(details: [
            'job1' => new VacancyDetail(id: 'job1', title: 'Senior PHP Dev', url: 'https://example.com/job1', description: 'Build things.'),
        ]);

        $tool = new GetVacancyDetailsTool($adapter);
        $output = $tool->execute(['vacancy_id' => 'job1']);

        $decoded = json_decode($output, true);
        $this->assertSame('Build things.', $decoded['description']);
    }

    public function test_execute_returns_error_string_for_unknown_vacancy(): void
    {
        $tool = new GetVacancyDetailsTool(new FakeJobSiteAdapter());
        $output = $tool->execute(['vacancy_id' => 'nope']);

        $this->assertStringStartsWith('Error:', $output);
    }
}
