<?php

namespace JobSearchAgent\Adapter\Upwork;

interface UpworkApiClientInterface
{
    /** @return array Raw decoded JSON: ['jobs' => [...]] */
    public function searchJobs(array $params): array;

    /** @return array Raw decoded JSON for one job */
    public function getJob(string $id): array;

    /** @return array Raw decoded JSON submission acknowledgement */
    public function submitProposal(string $jobId, array $payload): array;
}
