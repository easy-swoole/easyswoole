<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Support;

use EasySwoole\Command\Console\Console;
use EasySwoole\Command\Console\Terminal;

/** 使用编号输入测试命令流程，保证交互结果可重复。 */
class TestConsole extends Console
{
    public function __construct(private object $fixture)
    {
        parent::__construct(output: fopen('php://output', 'w'), terminal: new class extends Terminal {
            public function supported(): bool { return false; }
        });
    }
    public function interactive(): bool { return $this->fixture->interactive; }
    public function prompt(string $question): ?string
    {
        $this->fixture->prompts[] = $question;
        return array_shift($this->fixture->inputs);
    }
}
