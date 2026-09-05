<?php

namespace Tests\Store;

use JobSearchAgent\Store\ApplicationStore;
use PHPUnit\Framework\TestCase;

class ApplicationStoreTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/job-search-agent-store-' . uniqid();
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

    public function test_create_writes_a_record_with_status_found(): void
    {
        $store = new ApplicationStore($this->dir);
        $record = $store->create('upwork', 'job1', 'Senior PHP Laravel Developer');

        $this->assertSame('upwork', $record['platform']);
        $this->assertSame('job1', $record['vacancy_id']);
        $this->assertSame('found', $record['status']);
        $this->assertSame([], $record['stretches']);
        $this->assertFalse($record['prior_contact_checked']);
        $this->assertStringContainsString('upwork', $record['id']);
    }

    public function test_load_returns_the_same_data_that_was_created(): void
    {
        $store = new ApplicationStore($this->dir);
        $created = $store->create('upwork', 'job1', 'Senior PHP Laravel Developer');

        $loaded = $store->load($created['id']);

        $this->assertSame($created['id'], $loaded['id']);
        $this->assertSame('job1', $loaded['vacancy_id']);
    }

    public function test_load_throws_for_unknown_id(): void
    {
        $store = new ApplicationStore($this->dir);

        $this->expectException(\RuntimeException::class);
        $store->load('does-not-exist');
    }

    public function test_save_persists_status_changes(): void
    {
        $store = new ApplicationStore($this->dir);
        $record = $store->create('upwork', 'job1', 'Senior PHP Laravel Developer');

        $record['status'] = 'drafted';
        $store->save($record);

        $this->assertSame('drafted', $store->load($record['id'])['status']);
    }

    public function test_list_returns_every_stored_record(): void
    {
        $store = new ApplicationStore($this->dir);
        $store->create('upwork', 'job1', 'Senior PHP Laravel Developer');
        $store->create('upwork', 'job2', 'Backend Automation Engineer');

        $this->assertCount(2, $store->list());
    }

    public function test_create_avoids_id_collisions_for_same_title_same_day(): void
    {
        $store = new ApplicationStore($this->dir);
        $first = $store->create('upwork', 'job1', 'Backend Engineer');
        $second = $store->create('upwork', 'job2', 'Backend Engineer');

        $this->assertNotSame($first['id'], $second['id']);
    }
}
