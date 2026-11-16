<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkAction;
use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\TaskReloader;

abstract class TaskAction extends FrameworkAction
{
    protected function taskReloader(): TaskReloader
    {
        return new TaskReloader();
    }
    protected function signalTaskWorker(int $pid, int $signal): bool
    {
        return \Swoole\Process::kill($pid, $signal);
    }
    protected function requestStatus(): Package
    {
        return $this->runtime->call('taskStatus');
    }
}
