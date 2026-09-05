<?php

namespace Tests\Cli;

use JobSearchAgent\Cli\SubmitCommand;
use JobSearchAgent\Store\ApplicationStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\FakeJobSiteAdapter;

class SubmitCommandTest extends TestCase
{
    private string $dir;
    private ApplicationStore $store;
    private string $recordId;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/job-search-agent-submit-cmd-' . uniqid();
        mkdir($this->dir);
        $this->store = new ApplicationStore($this->dir);
        $record = $this->store->create('upwork', 'job1', 'Senior PHP Laravel Developer');
        $this->recordId = $record['id'];
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

    private function tester(FakeJobSiteAdapter $adapter): CommandTester
    {
        $command = new SubmitCommand($this->store, $adapter);
        $application = new Application();
        $application->add($command);

        return new CommandTester($command);
    }

    public function test_refuses_to_submit_without_confirmation(): void
    {
        $adapter = new FakeJobSiteAdapter();
        $tester = $this->tester($adapter);

        $tester->setInputs(['no']);
        $tester->execute(['application-id' => $this->recordId]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Cancelled', $tester->getDisplay());
        $this->assertCount(0, $adapter->submittedApplications());
        $this->assertSame('found', $this->store->load($this->recordId)['status']);
    }

    public function test_submits_with_yes_flag_and_updates_status(): void
    {
        $adapter = new FakeJobSiteAdapter();
        $tester = $this->tester($adapter);

        $tester->execute(['application-id' => $this->recordId, '--yes' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertCount(1, $adapter->submittedApplications());
        $this->assertSame('job1', $adapter->submittedApplications()[0]->vacancyId);
        $this->assertSame('submitted', $this->store->load($this->recordId)['status']);
    }

    public function test_confirming_interactively_also_submits(): void
    {
        $adapter = new FakeJobSiteAdapter();
        $tester = $this->tester($adapter);

        $tester->setInputs(['yes']);
        $tester->execute(['application-id' => $this->recordId]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertCount(1, $adapter->submittedApplications());
    }

    public function test_already_submitted_is_a_noop(): void
    {
        $record = $this->store->load($this->recordId);
        $record['status'] = 'submitted';
        $this->store->save($record);

        $adapter = new FakeJobSiteAdapter();
        $tester = $this->tester($adapter);

        $tester->execute(['application-id' => $this->recordId, '--yes' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('already submitted', $tester->getDisplay());
        $this->assertCount(0, $adapter->submittedApplications());
    }

    public function test_unknown_id_fails_clearly(): void
    {
        $adapter = new FakeJobSiteAdapter();
        $tester = $this->tester($adapter);

        $tester->execute(['application-id' => 'does-not-exist', '--yes' => true]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertCount(0, $adapter->submittedApplications());
    }
}
