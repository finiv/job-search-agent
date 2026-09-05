<?php

namespace Tests\Support;

use JobSearchAgent\Adapter\Upwork\UpworkApiClientInterface;

class FakeUpworkApiClient implements UpworkApiClientInterface
{
    public function __construct(
        private readonly array $searchResponse = ['jobs' => []],
        private readonly array $jobResponses = [],
        private readonly array $submitResponse = ['success' => true, 'message' => 'ok', 'proposal_id' => 'ext_1'],
    ) {
    }

    public function searchJobs(array $params): array
    {
        return $this->searchResponse;
    }

    public function getJob(string $id): array
    {
        if (!isset($this->jobResponses[$id])) {
            throw new \RuntimeException("No fake job response for id: {$id}");
        }

        return $this->jobResponses[$id];
    }

    public function submitProposal(string $jobId, array $payload): array
    {
        return $this->submitResponse;
    }
}
