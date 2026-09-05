<?php

namespace JobSearchAgent\Http;

class AnthropicHttpClient implements AnthropicClientInterface
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    public function __construct(private readonly string $apiKey)
    {
    }

    public function createMessage(array $params): array
    {
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::API_VERSION,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($params, JSON_THROW_ON_ERROR),
        ]);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new AnthropicApiException("HTTP request to Anthropic API failed: {$error}");
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode((string) $body, true);

        if ($status >= 400) {
            $message = $decoded['error']['message'] ?? (string) $body;
            throw new AnthropicApiException("Anthropic API error ({$status}): {$message}");
        }

        if ($decoded === null && trim((string) $body) !== 'null') {
            throw new AnthropicApiException("Anthropic API returned invalid JSON: " . substr((string) $body, 0, 200));
        }

        return $decoded ?? [];
    }
}
