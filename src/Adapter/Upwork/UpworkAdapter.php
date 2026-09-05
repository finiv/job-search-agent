<?php

namespace JobSearchAgent\Adapter\Upwork;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;
use JobSearchAgent\Adapter\Value\Application;
use JobSearchAgent\Adapter\Value\SearchCriteria;
use JobSearchAgent\Adapter\Value\SubmissionResult;
use JobSearchAgent\Adapter\Value\Vacancy;
use JobSearchAgent\Adapter\Value\VacancyDetail;

class UpworkAdapter implements JobSiteAdapterInterface
{
    public function __construct(private readonly UpworkApiClientInterface $client)
    {
    }

    public function searchVacancies(SearchCriteria $criteria): array
    {
        $raw = $this->client->searchJobs(['q' => $criteria->query]);

        return array_map(
            static fn (array $job) => new Vacancy(
                id: (string) ($job['id'] ?? ''),
                title: (string) ($job['title'] ?? ''),
                url: (string) ($job['url'] ?? ''),
                budget: $job['budget'] ?? null,
                snippet: $job['snippet'] ?? null,
            ),
            $raw['jobs'] ?? []
        );
    }

    public function getVacancyDetails(string $vacancyId): VacancyDetail
    {
        $raw = $this->client->getJob($vacancyId);

        return new VacancyDetail(
            id: (string) ($raw['id'] ?? ''),
            title: (string) ($raw['title'] ?? ''),
            url: (string) ($raw['url'] ?? ''),
            description: (string) ($raw['description'] ?? ''),
            budget: $raw['budget'] ?? null,
            skills: $raw['skills'] ?? [],
        );
    }

    public function submitApplication(Application $application): SubmissionResult
    {
        $raw = $this->client->submitProposal($application->vacancyId, [
            'cover_letter' => $application->coverLetter,
        ]);

        return new SubmissionResult(
            success: (bool) ($raw['success'] ?? false),
            message: (string) ($raw['message'] ?? ''),
            externalId: $raw['proposal_id'] ?? null,
        );
    }
}
