<?php

namespace Tests\Cli;

use JobSearchAgent\Cli\PrepCommand;
use JobSearchAgent\Store\ApplicationStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PrepCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/job-search-agent-prep-cmd-' . uniqid();
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

    public function test_marks_an_application_as_drafted(): void
    {
        $store = new ApplicationStore($this->dir);
        $record = $store->create('upwork', 'job1', 'Senior PHP Dev');

        $command = new PrepCommand($store);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['application-id' => $record['id']]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame('drafted', $store->load($record['id'])['status']);
    }

    public function test_unknown_id_fails_clearly(): void
    {
        $store = new ApplicationStore($this->dir);
        $command = new PrepCommand($store);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['application-id' => 'does-not-exist']);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('No application record found', $tester->getDisplay());
    }
}
