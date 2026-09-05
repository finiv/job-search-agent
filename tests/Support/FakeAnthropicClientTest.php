<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

class FakeAnthropicClientTest extends TestCase
{
    public function test_returns_scripted_responses_in_order_and_records_requests(): void
    {
        $client = new FakeAnthropicClient([
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'first']]],
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'second']]],
        ]);

        $first = $client->createMessage(['model' => 'claude-sonnet-5']);
        $second = $client->createMessage(['model' => 'claude-sonnet-5']);

        $this->assertSame('first', $first['content'][0]['text']);
        $this->assertSame('second', $second['content'][0]['text']);
        $this->assertCount(2, $client->requests());
    }

    public function test_throws_when_scripted_responses_are_exhausted(): void
    {
        $client = new FakeAnthropicClient([]);

        $this->expectException(\RuntimeException::class);
        $client->createMessage(['model' => 'claude-sonnet-5']);
    }
}
