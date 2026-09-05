<?php

namespace JobSearchAgent\Http;

interface AnthropicClientInterface
{
    /**
     * @param array $params Full Anthropic Messages API request body (model, max_tokens, system, messages, tools, ...)
     * @return array Decoded JSON response body
     */
    public function createMessage(array $params): array;
}
