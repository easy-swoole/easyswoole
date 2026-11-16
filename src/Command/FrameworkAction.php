<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Input\Input;
use EasySwoole\Command\Console\Console;

abstract class FrameworkAction
{
    protected Console $console;
    public function __construct(protected ?RuntimeClient $runtime = null)
    { $this->runtime ??= new RuntimeClient(); }
    abstract public static function createDefinition(): ActionDefinition;
    public function definition(): ActionDefinition { return static::createDefinition(); }
    final public function execute(Input $input, Console $console): CommandResult
    {
        $this->console = $console;
        return $this->handle($input);
    }
    abstract protected function handle(Input $input): CommandResult;
    protected function isInteractive(): bool { return $this->console->interactive(); }
    protected function readInput(string $prompt): ?string { return $this->console->prompt($prompt); }
}
