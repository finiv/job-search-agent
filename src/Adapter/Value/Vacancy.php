<?php

namespace JobSearchAgent\Adapter\Value;

class Vacancy
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $url,
        public readonly ?string $budget = null,
        public readonly ?string $snippet = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'budget' => $this->budget,
            'snippet' => $this->snippet,
        ];
    }
}
