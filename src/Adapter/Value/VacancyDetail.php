<?php

namespace JobSearchAgent\Adapter\Value;

class VacancyDetail
{
    /** @param string[] $skills */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $url,
        public readonly string $description,
        public readonly ?string $budget = null,
        public readonly array $skills = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'budget' => $this->budget,
            'skills' => $this->skills,
        ];
    }
}
