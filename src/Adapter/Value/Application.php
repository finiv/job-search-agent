<?php

namespace JobSearchAgent\Adapter\Value;

class Application
{
    public function __construct(
        public readonly string $vacancyId,
        public readonly string $coverLetter,
        public readonly ?string $cvPath = null,
    ) {
    }
}
