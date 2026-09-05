<?php

namespace Tests\Support;

use JobSearchAgent\Http\AnthropicClientInterface;

class FakeAnthropicClient implements AnthropicClientInterface
{
    private array $recordedRequests = [];

    public function __construct(private array $responses)
    {
    }

    public function createMessage(array $params): array
    {
        $this->recordedRequests[] = $params;

        if (empty($this->responses)) {
            throw new \RuntimeException('FakeAnthropicClient: no more scripted responses');
        }

        return array_shift($this->responses);
    }

    public function requests(): array
    {
        return $this->recordedRequests;
    }
}
