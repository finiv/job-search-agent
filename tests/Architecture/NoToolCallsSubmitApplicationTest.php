<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

class NoToolCallsSubmitApplicationTest extends TestCase
{
    public function test_no_tool_implementation_calls_submit_application(): void
    {
        $toolFiles = glob(dirname(__DIR__, 2) . '/src/Tool/*.php') ?: [];
        $this->assertNotEmpty($toolFiles, 'Expected to find tool files to check.');

        foreach ($toolFiles as $file) {
            $contents = (string) file_get_contents($file);
            $this->assertStringNotContainsString(
                'submitApplication',
                $contents,
                "src/Tool/ files must never call submitApplication() — {$file} does."
            );
        }
    }
}
