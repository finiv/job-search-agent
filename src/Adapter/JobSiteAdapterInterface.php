<?php

namespace JobSearchAgent\Adapter;

use JobSearchAgent\Adapter\Value\Application;
use JobSearchAgent\Adapter\Value\SearchCriteria;
use JobSearchAgent\Adapter\Value\SubmissionResult;
use JobSearchAgent\Adapter\Value\Vacancy;
use JobSearchAgent\Adapter\Value\VacancyDetail;

interface JobSiteAdapterInterface
{
    /** @return Vacancy[] */
    public function searchVacancies(SearchCriteria $criteria): array;

    public function getVacancyDetails(string $vacancyId): VacancyDetail;

    public function submitApplication(Application $application): SubmissionResult;
}
