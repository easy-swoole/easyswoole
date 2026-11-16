<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Command\Console\Console;
use EasySwoole\Command\Contracts\CommandInterface;
use EasySwoole\Command\Definition\CommandDefinition;
use EasySwoole\Command\Exception\InputException;
use EasySwoole\Command\Input\Input;
use EasySwoole\Command\Result\CommandResult;

/** 主框架命令统一持有操作处理器，帮助只读取静态定义，不初始化业务依赖。 */
abstract class FrameworkCommand implements CommandInterface
{
    protected const NAME = '';
    protected const DESCRIPTION = '';
    protected const ACTIONS = [];
    protected const STANDALONE = null;

    /** @param array<string, FrameworkAction> $handlers 可注入单个操作的业务依赖或测试替身。 */
    public function __construct(private array $handlers = [])
    {
        $known = static::ACTIONS ?: ['' => static::STANDALONE];
        foreach ($handlers as $name => $handler) {
            $expected = $known[$name] ?? null;
            if ($expected === null || !$handler instanceof $expected) {
                throw new \InvalidArgumentException('invalid action handler: ' . $name);
            }
        }
    }

    public static function createDefinition(): CommandDefinition
    {
        $definition = CommandDefinition::create(static::NAME)->description(static::DESCRIPTION);
        foreach (static::ACTIONS as $name => $class) {
            $action = $class::createDefinition();
            if ($action->name !== $name) { throw new \LogicException('action definition does not match handler key: ' . $name); }
            $definition->action($action);
        }
        if (static::STANDALONE !== null) {
            $class = static::STANDALONE;
            $standalone = $class::createDefinition();
            foreach ($standalone->options() as $option) { $definition->option($option); }
            foreach ($standalone->arguments() as $argument) { $definition->argument($argument); }
        }
        return $definition;
    }

    public function definition(): CommandDefinition { return static::createDefinition(); }

    public function execute(Input $input, Console $console): CommandResult
    {
        $name = $input->action() ?? '';
        $class = static::ACTIONS[$name] ?? ($name === '' ? static::STANDALONE : null);
        if ($class === null) { throw new InputException('unknown action: ' . $name); }
        $handler = $this->handlers[$name] ??= new $class();
        return $handler->execute($input, $console);
    }
}
