<?php

namespace JobSearchAgent\Adapter\Value;

class SearchCriteria
{
    public function __construct(
        public readonly string $query,
    ) {
    }
}
