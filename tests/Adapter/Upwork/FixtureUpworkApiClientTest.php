<?php

namespace Tests\Adapter\Upwork;

use JobSearchAgent\Adapter\Upwork\FixtureUpworkApiClient;
use PHPUnit\Framework\TestCase;

class FixtureUpworkApiClientTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = dirname(__DIR__, 2) . '/fixtures/upwork';
    }

    public function test_search_jobs_returns_matching_fixture_by_query(): void
    {
        $client = new FixtureUpworkApiClient($this->fixturesDir);
        $result = $client->searchJobs(['q' => 'backend php laravel']);

        $this->assertCount(2, $result['jobs']);
        $this->assertSame('job1', $result['jobs'][0]['id']);
    }

    public function test_search_jobs_falls_back_to_default_for_unknown_query(): void
    {
        $client = new FixtureUpworkApiClient($this->fixturesDir);
        $result = $client->searchJobs(['q' => 'something with no fixture']);

        $this->assertSame(['jobs' => []], $result);
    }

    public function test_get_job_returns_the_matching_fixture(): void
    {
        $client = new FixtureUpworkApiClient($this->fixturesDir);
        $job = $client->getJob('job1');

        $this->assertSame('Senior PHP Laravel Developer', $job['title']);
    }

    public function test_get_job_throws_for_unknown_id(): void
    {
        $client = new FixtureUpworkApiClient($this->fixturesDir);

        $this->expectException(\RuntimeException::class);
        $client->getJob('does-not-exist');
    }

    public function test_submit_proposal_falls_back_to_default_acknowledgement(): void
    {
        $client = new FixtureUpworkApiClient($this->fixturesDir);
        $result = $client->submitProposal('job1', ['cover_letter' => 'Hello']);

        $this->assertTrue($result['success']);
        $this->assertSame('prop_9001', $result['proposal_id']);
    }
}
