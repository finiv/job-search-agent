<?php

namespace Tests\Cli;

use JobSearchAgent\Cli\SearchCommand;
use JobSearchAgent\Store\ApplicationStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\FakeAnthropicClient;
use Tests\Support\FakeJobSiteAdapter;

class SearchCommandTest extends TestCase
{
    private string $applicationsDir;

    protected function setUp(): void
    {
        $this->applicationsDir = sys_get_temp_dir() . '/job-search-agent-search-cmd-' . uniqid();
        mkdir($this->applicationsDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->applicationsDir . '/*/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->applicationsDir . '/*') ?: [] as $dir) {
            @rmdir($dir);
        }
        rmdir($this->applicationsDir);
    }

    private function tester(FakeAnthropicClient $client, FakeJobSiteAdapter $adapter, ApplicationStore $store): CommandTester
    {
        $command = new SearchCommand($client, $adapter, $store);
        $application = new Application();
        $application->add($command);

        return new CommandTester($command);
    }

    public function test_shortlists_and_writes_application_records(): void
    {
        $client = new FakeAnthropicClient([
            [
                'stop_reason' => 'end_turn',
                'content' => [
                    ['type' => 'text', 'text' => json_encode([
                        'shortlist' => [
                            ['vacancy_id' => 'job1', 'title' => 'Senior PHP Laravel Developer', 'reason' => 'Strong backend match.'],
                        ],
                    ])],
                ],
            ],
        ]);

        $store = new ApplicationStore($this->applicationsDir);
        $tester = $this->tester($client, new FakeJobSiteAdapter(), $store);
        $tester->execute(['query' => 'backend php laravel']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Senior PHP Laravel Developer', $tester->getDisplay());

        $records = $store->list();
        $this->assertCount(1, $records);
        $this->assertSame('job1', $records[0]['vacancy_id']);
        $this->assertSame('found', $records[0]['status']);
    }

    public function test_empty_shortlist_reports_success_and_writes_nothing(): void
    {
        $client = new FakeAnthropicClient([
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => json_encode(['shortlist' => []])]]],
        ]);

        $store = new ApplicationStore($this->applicationsDir);
        $tester = $this->tester($client, new FakeJobSiteAdapter(), $store);
        $tester->execute(['query' => 'irrelevant query']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('No vacancies shortlisted', $tester->getDisplay());
        $this->assertCount(0, $store->list());
    }

    public function test_rejects_unsupported_platform(): void
    {
        $client = new FakeAnthropicClient([]);
        $store = new ApplicationStore($this->applicationsDir);
        $tester = $this->tester($client, new FakeJobSiteAdapter(), $store);
        $tester->execute(['query' => 'php', '--platform' => 'wellfound']);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Unsupported platform', $tester->getDisplay());
    }
}
