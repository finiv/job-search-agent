<?php

namespace JobSearchAgent\Adapter\Upwork;

class FixtureUpworkApiClient implements UpworkApiClientInterface
{
    public function __construct(private readonly string $fixturesDir)
    {
    }

    public function searchJobs(array $params): array
    {
        $query = (string) ($params['q'] ?? '');
        $path = $this->fixturesDir . '/search__' . $this->slug($query) . '.json';

        return $this->readJson($path, $this->fixturesDir . '/search__default.json');
    }

    public function getJob(string $id): array
    {
        return $this->readJson($this->fixturesDir . "/job__{$id}.json");
    }

    public function submitProposal(string $jobId, array $payload): array
    {
        $path = $this->fixturesDir . "/submit__{$jobId}.json";

        return $this->readJson($path, $this->fixturesDir . '/submit__default.json');
    }

    private function slug(string $value): string
    {
        $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'default';
    }

    private function readJson(string $path, ?string $fallback = null): array
    {
        if (!is_file($path) && $fallback !== null && is_file($fallback)) {
            $path = $fallback;
        }

        if (!is_file($path)) {
            throw new \RuntimeException("Fixture not found: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }
}
