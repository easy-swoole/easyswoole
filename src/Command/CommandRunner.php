<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Command\Application;
use EasySwoole\Command\Definition\CommandDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Console\Console;
use EasySwoole\Command\Input\Input;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Core;

class CommandRunner extends Application
{
    use Singleton;

    public function __construct()
    {
        parent::__construct('EasySwoole');
        $this->addProvider(new DefaultCommandProvider());
        $this->middleware(function (CommandDefinition $definition, Input $input, Console $console, callable $next): CommandResult {
            $action = $definition->actionDefinition($input->action());
            if ($action?->meta('runtime') ?? $definition->meta('runtime')) {
                Utility::opCacheClear();
                $this->initializeRuntime($input->option('mode'));
            }
            if (!($action?->meta('watch') ?? $definition->meta('watch')) || !$input->provided('watch')) { return $next(); }
            $latest = CommandResult::success();
            $console->live(function () use ($next, &$latest) {
                $latest = $next();
                return $latest->exitCode === 0 ? ($latest->message ?? '') : false;
            }, $input->option('watch'));
            // 成功的实时帧已经显示；错误消息交由 Application 输出。
            if ($latest->exitCode === 0) { $latest->message = null; }
            return $latest;
        });
    }

    protected function initializeRuntime(?string $mode): void { Core::getInstance()->initialize($mode); }
}
