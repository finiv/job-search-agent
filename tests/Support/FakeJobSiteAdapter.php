<?php

namespace Tests\Support;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;
use JobSearchAgent\Adapter\Value\Application;
use JobSearchAgent\Adapter\Value\SearchCriteria;
use JobSearchAgent\Adapter\Value\SubmissionResult;
use JobSearchAgent\Adapter\Value\Vacancy;
use JobSearchAgent\Adapter\Value\VacancyDetail;

class FakeJobSiteAdapter implements JobSiteAdapterInterface
{
    private array $submittedApplications = [];

    /**
     * @param Vacancy[] $vacancies
     * @param array<string, VacancyDetail> $details
     */
    public function __construct(
        private readonly array $vacancies = [],
        private readonly array $details = [],
        private readonly ?SubmissionResult $submissionResult = null,
    ) {
    }

    public function searchVacancies(SearchCriteria $criteria): array
    {
        return $this->vacancies;
    }

    public function getVacancyDetails(string $vacancyId): VacancyDetail
    {
        if (!isset($this->details[$vacancyId])) {
            throw new \RuntimeException("No fixture detail for vacancy: {$vacancyId}");
        }

        return $this->details[$vacancyId];
    }

    public function submitApplication(Application $application): SubmissionResult
    {
        $this->submittedApplications[] = $application;

        return $this->submissionResult ?? new SubmissionResult(true, 'ok', 'ext_1');
    }

    /** @return Application[] */
    public function submittedApplications(): array
    {
        return $this->submittedApplications;
    }
}
