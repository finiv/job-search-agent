<?php

namespace Tests\Adapter\Upwork;

use JobSearchAgent\Adapter\Upwork\UpworkAdapter;
use JobSearchAgent\Adapter\Value\Application;
use JobSearchAgent\Adapter\Value\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeUpworkApiClient;

class UpworkAdapterTest extends TestCase
{
    public function test_search_vacancies_maps_raw_jobs_to_value_objects(): void
    {
        $client = new FakeUpworkApiClient(searchResponse: [
            'jobs' => [
                ['id' => 'job1', 'title' => 'Senior PHP Dev', 'url' => 'https://example.com/job1', 'budget' => '$40/hr', 'snippet' => 'Nice role.'],
            ],
        ]);

        $adapter = new UpworkAdapter($client);
        $vacancies = $adapter->searchVacancies(new SearchCriteria('php'));

        $this->assertCount(1, $vacancies);
        $this->assertSame('job1', $vacancies[0]->id);
        $this->assertSame('Senior PHP Dev', $vacancies[0]->title);
        $this->assertSame('$40/hr', $vacancies[0]->budget);
    }

    public function test_get_vacancy_details_maps_raw_job_to_value_object(): void
    {
        $client = new FakeUpworkApiClient(jobResponses: [
            'job1' => [
                'id' => 'job1',
                'title' => 'Senior PHP Dev',
                'url' => 'https://example.com/job1',
                'description' => 'Build things.',
                'budget' => '$40/hr',
                'skills' => ['PHP', 'Laravel'],
            ],
        ]);

        $adapter = new UpworkAdapter($client);
        $detail = $adapter->getVacancyDetails('job1');

        $this->assertSame('Build things.', $detail->description);
        $this->assertSame(['PHP', 'Laravel'], $detail->skills);
    }

    public function test_submit_application_maps_raw_response_to_submission_result(): void
    {
        $client = new FakeUpworkApiClient(submitResponse: [
            'success' => true,
            'message' => 'Proposal submitted.',
            'proposal_id' => 'prop_1',
        ]);

        $adapter = new UpworkAdapter($client);
        $result = $adapter->submitApplication(new Application(vacancyId: 'job1', coverLetter: 'Hello'));

        $this->assertTrue($result->success);
        $this->assertSame('prop_1', $result->externalId);
    }
}
