<?php

namespace JobSearchAgent\Store;

class ApplicationStore
{
    public function __construct(private readonly string $applicationsDir)
    {
    }

    public function create(string $platform, string $vacancyId, string $vacancyTitle): array
    {
        $now = (new \DateTimeImmutable())->format(DATE_ATOM);

        $record = [
            'id' => $this->makeId($platform, $vacancyTitle),
            'platform' => $platform,
            'vacancy_id' => $vacancyId,
            'status' => 'found',
            'cv_version' => null,
            'stretches' => [],
            'prior_contact_checked' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->save($record);

        return $record;
    }

    public function load(string $id): array
    {
        $path = $this->recordPath($id);

        if (!is_file($path)) {
            throw new \RuntimeException("No application record found for id: {$id}");
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new \RuntimeException("Corrupt application record: {$id}");
        }

        return $decoded;
    }

    public function save(array $record): void
    {
        $record['updated_at'] = (new \DateTimeImmutable())->format(DATE_ATOM);
        $dir = $this->applicationsDir . '/' . $record['id'];

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException("Unable to create application directory: {$dir}");
        }

        file_put_contents(
            $dir . '/record.json',
            json_encode($record, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        );
    }

    /** @return array[] */
    public function list(): array
    {
        if (!is_dir($this->applicationsDir)) {
            return [];
        }

        $records = [];
        foreach (glob($this->applicationsDir . '/*/record.json') ?: [] as $path) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $records[] = $decoded;
            }
        }

        return $records;
    }

    private function recordPath(string $id): string
    {
        return $this->applicationsDir . '/' . $id . '/record.json';
    }

    private function makeId(string $platform, string $vacancyTitle): string
    {
        $date = (new \DateTimeImmutable())->format('Y-m-d');
        $slug = $this->slugify($vacancyTitle);
        $base = "{$date}-{$platform}-{$slug}";

        $id = $base;
        $suffix = 2;
        while (is_dir($this->applicationsDir . '/' . $id)) {
            $id = "{$base}-{$suffix}";
            $suffix++;
        }

        return $id;
    }

    private function slugify(string $value): string
    {
        $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'vacancy';
    }
}
