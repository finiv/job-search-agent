<?php

namespace JobSearchAgent\Agent;

class AgentResult
{
    /** @param array $rawContent The final assistant message's raw content blocks */
    public function __construct(
        public readonly string $text,
        public readonly array $rawContent,
    ) {
    }
}
