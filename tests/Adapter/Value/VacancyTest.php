<?php

namespace Tests\Adapter\Value;

use JobSearchAgent\Adapter\Value\Vacancy;
use PHPUnit\Framework\TestCase;

class VacancyTest extends TestCase
{
    public function test_to_array_includes_all_fields(): void
    {
        $vacancy = new Vacancy(id: 'job1', title: 'Senior PHP Dev', url: 'https://example.com/job1', budget: '$40-65/hr', snippet: 'Great role.');

        $this->assertSame([
            'id' => 'job1',
            'title' => 'Senior PHP Dev',
            'url' => 'https://example.com/job1',
            'budget' => '$40-65/hr',
            'snippet' => 'Great role.',
        ], $vacancy->toArray());
    }

    public function test_optional_fields_default_to_null(): void
    {
        $vacancy = new Vacancy(id: 'job1', title: 'Senior PHP Dev', url: 'https://example.com/job1');

        $this->assertNull($vacancy->toArray()['budget']);
        $this->assertNull($vacancy->toArray()['snippet']);
    }
}
