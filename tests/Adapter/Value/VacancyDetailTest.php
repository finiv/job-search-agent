<?php

namespace Tests\Adapter\Value;

use JobSearchAgent\Adapter\Value\VacancyDetail;
use PHPUnit\Framework\TestCase;

class VacancyDetailTest extends TestCase
{
    public function test_to_array_includes_all_fields(): void
    {
        $detail = new VacancyDetail(
            id: 'job1',
            title: 'Senior PHP Dev',
            url: 'https://example.com/job1',
            description: 'Build things.',
            budget: '$40-65/hr',
            skills: ['PHP', 'Laravel'],
        );

        $this->assertSame([
            'id' => 'job1',
            'title' => 'Senior PHP Dev',
            'url' => 'https://example.com/job1',
            'description' => 'Build things.',
            'budget' => '$40-65/hr',
            'skills' => ['PHP', 'Laravel'],
        ], $detail->toArray());
    }
}
