<?php

namespace JobSearchAgent\Agent;

use JobSearchAgent\Http\AnthropicClientInterface;
use JobSearchAgent\Tool\ToolInterface;

class AgentLoop
{
    /** @param ToolInterface[] $tools */
    public function __construct(
        private readonly AnthropicClientInterface $client,
        private readonly string $systemPrompt,
        private readonly array $tools = [],
        private readonly string $model = 'claude-sonnet-5',
        private readonly int $maxTurns = 15,
    ) {
    }

    public function run(string $userMessage): AgentResult
    {
        $toolsByName = [];
        foreach ($this->tools as $tool) {
            $toolsByName[$tool->name()] = $tool;
        }

        $toolSchemas = array_map(
            static fn (ToolInterface $tool) => [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'input_schema' => $tool->inputSchema(),
            ],
            $this->tools
        );

        $messages = [
            ['role' => 'user', 'content' => $userMessage],
        ];

        for ($turn = 0; $turn < $this->maxTurns; $turn++) {
            $response = $this->client->createMessage([
                'model' => $this->model,
                'max_tokens' => 8192,
                'system' => $this->systemPrompt,
                'messages' => $messages,
                'tools' => $toolSchemas,
            ]);

            $content = $response['content'] ?? [];
            $messages[] = ['role' => 'assistant', 'content' => $content];

            $stopReason = $response['stop_reason'] ?? null;

            if ($stopReason === 'max_tokens') {
                throw new AgentLoopException(
                    'Response was truncated at the token limit before completing — '
                    . 'try a shorter request or increase max_tokens.'
                );
            }

            if ($stopReason !== 'tool_use') {
                return new AgentResult($this->extractText($content), $content);
            }

            $toolResults = [];
            foreach ($content as $block) {
                if (($block['type'] ?? null) !== 'tool_use') {
                    continue;
                }

                $tool = $toolsByName[$block['name']] ?? null;
                $output = $tool !== null
                    ? $tool->execute($block['input'] ?? [])
                    : "Error: unknown tool '{$block['name']}'";

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $output,
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }

        throw new AgentLoopException("Exceeded max turns ({$this->maxTurns}) without reaching end_turn");
    }

    private function extractText(array $content): string
    {
        $text = '';
        foreach ($content as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text .= $block['text'];
            }
        }
        return $text;
    }
}
