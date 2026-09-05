<?php

namespace Tests\Support;

use JobSearchAgent\Tool\ToolInterface;

class EchoTool implements ToolInterface
{
    public function name(): string
    {
        return 'echo';
    }

    public function description(): string
    {
        return 'Echoes back whatever "value" was passed in.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['value' => ['type' => 'string']],
            'required' => ['value'],
        ];
    }

    public function execute(array $input): string
    {
        return 'echoed: ' . (string) ($input['value'] ?? '');
    }
}
