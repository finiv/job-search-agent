<?php

namespace Tests\Cli;

use JobSearchAgent\Cli\ReviewCommand;
use JobSearchAgent\Store\ApplicationStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ReviewCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/job-search-agent-review-cmd-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->dir . '/*') ?: [] as $subdir) {
            @rmdir($subdir);
        }
        rmdir($this->dir);
    }

    public function test_prints_the_stored_record(): void
    {
        $store = new ApplicationStore($this->dir);
        $record = $store->create('upwork', 'job1', 'Senior PHP Dev');

        $command = new ReviewCommand($store);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['application-id' => $record['id']]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('"vacancy_id": "job1"', $tester->getDisplay());
    }
}
