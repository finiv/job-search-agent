<?php

namespace JobSearchAgent\Adapter\Value;

class SubmissionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $externalId = null,
    ) {
    }
}
