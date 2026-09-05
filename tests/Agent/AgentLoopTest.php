<?php

namespace Tests\Agent;

use JobSearchAgent\Agent\AgentLoop;
use JobSearchAgent\Agent\AgentLoopException;
use PHPUnit\Framework\TestCase;
use Tests\Support\EchoTool;
use Tests\Support\FakeAnthropicClient;

class AgentLoopTest extends TestCase
{
    public function test_single_turn_end_turn_response_returns_text(): void
    {
        $client = new FakeAnthropicClient([
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'all done']]],
        ]);

        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: []);
        $result = $loop->run('do the thing');

        $this->assertSame('all done', $result->text);
    }

    public function test_executes_tool_use_and_sends_result_back(): void
    {
        $client = new FakeAnthropicClient([
            [
                'stop_reason' => 'tool_use',
                'content' => [
                    ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'echo', 'input' => ['value' => 'hi']],
                ],
            ],
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'finished']]],
        ]);

        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: [new EchoTool()]);
        $result = $loop->run('do the thing');

        $this->assertSame('finished', $result->text);

        $secondRequest = $client->requests()[1];
        $toolResultMessage = end($secondRequest['messages']);
        $this->assertSame('user', $toolResultMessage['role']);
        $this->assertSame('toolu_1', $toolResultMessage['content'][0]['tool_use_id']);
        $this->assertSame('echoed: hi', $toolResultMessage['content'][0]['content']);
    }

    public function test_executes_multiple_tool_calls_requested_in_the_same_turn(): void
    {
        $client = new FakeAnthropicClient([
            [
                'stop_reason' => 'tool_use',
                'content' => [
                    ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'echo', 'input' => ['value' => 'a']],
                    ['type' => 'tool_use', 'id' => 'toolu_2', 'name' => 'echo', 'input' => ['value' => 'b']],
                ],
            ],
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'finished']]],
        ]);

        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: [new EchoTool()]);
        $loop->run('do the thing');

        $this->assertCount(2, $client->requests(), 'Both tool calls should resolve in one extra turn, not two.');

        $secondRequest = $client->requests()[1];
        $toolResultMessage = end($secondRequest['messages']);
        $this->assertCount(2, $toolResultMessage['content']);
    }

    public function test_unknown_tool_returns_error_without_crashing(): void
    {
        $client = new FakeAnthropicClient([
            [
                'stop_reason' => 'tool_use',
                'content' => [['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'nonexistent', 'input' => []]],
            ],
            ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'done']]],
        ]);

        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: []);
        $loop->run('do the thing');

        $secondRequest = $client->requests()[1];
        $toolResultMessage = end($secondRequest['messages']);
        $this->assertStringContainsString("unknown tool 'nonexistent'", $toolResultMessage['content'][0]['content']);
    }

    public function test_max_tokens_truncation_throws_a_clear_error(): void
    {
        $client = new FakeAnthropicClient([
            ['stop_reason' => 'max_tokens', 'content' => [['type' => 'text', 'text' => 'cut off mid-']]],
        ]);

        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: []);

        $this->expectException(AgentLoopException::class);
        $this->expectExceptionMessageMatches('/truncat/i');
        $loop->run('do the thing');
    }

    public function test_exceeds_max_turns_throws(): void
    {
        $alwaysToolUse = [
            'stop_reason' => 'tool_use',
            'content' => [['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'echo', 'input' => ['value' => 'x']]],
        ];

        $client = new FakeAnthropicClient([$alwaysToolUse, $alwaysToolUse]);
        $loop = new AgentLoop($client, systemPrompt: 'You are a test agent.', tools: [new EchoTool()], maxTurns: 2);

        $this->expectException(AgentLoopException::class);
        $loop->run('do the thing');
    }
}
