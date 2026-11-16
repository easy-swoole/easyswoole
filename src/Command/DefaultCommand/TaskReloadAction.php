<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Exception\InterruptedException;
use EasySwoole\Command\Input\Input;
use EasySwoole\Bridge\StatusEnum;

class TaskReloadAction extends TaskAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('reload')
            ->description('reload EasySwoole custom Task workers')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::flag('force', 'use SIGKILL; default SIGTERM'))
            ->option(Option::flag('rolling', 'restart one Task worker at a time'))
            ->option(Option::number('timeout', 'per-worker restart timeout in seconds; requires --rolling', default: 30)
                ->validate(static fn ($value) => $value > 0 ? true : 'timeout must be positive'))
            ->validateInput(static fn (Input $input) => !$input->optionProvided('timeout') || $input->option('rolling')
                ? true : '--timeout requires --rolling')
            ->metadata('runtime', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $signal = $input->option('force') ? SIGKILL : SIGTERM;
        if ($input->option('rolling')) {
            $reloader = $this->taskReloader();
            try {
                $rows = $this->runtime->run(fn () => $reloader->reload($signal, (float) $input->option('timeout')));
            } catch (\Throwable $error) {
                $completed = $reloader->completed();
                if ($error instanceof InterruptedException || !$completed) {
                    throw $error;
                }
                // 滚动重启已经完成的操作需要随失败结果返回。
                return CommandResult::failure($error->getMessage(), data: $completed);
            }
            $messages = [];
            foreach ($rows as $index => $row) {
                $messages[] = "Task Worker {$index}: PID {$row['oldPid']} -> {$row['pid']} ready";
            }
            return CommandResult::success(
                $messages ? implode(PHP_EOL, $messages) : 'no Task workers available to reload',
                $rows,
            );
        }
        $package = $this->runtime->call('taskWorkerPidList');
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            $result->exitCode = 1;
            $result->message = $package->getMsg();
            return $result;
        }
        $pids = $package->getArgs();
        if (!is_array($pids)) {
            throw new \UnexpectedValueException('invalid Task worker PID list');
        }
        foreach ($pids as $pid) {
            if (!is_int($pid) || $pid <= 0) {
                throw new \UnexpectedValueException('invalid Task worker PID');
            }
        }
        $sent = [];
        $failed = [];
        foreach (array_unique($pids) as $pid) {
            if ($this->signalTaskWorker($pid, $signal)) {
                $sent[] = $pid;
            } else {
                $failed[] = $pid;
            }
        }
        $result->data = ['signal' => $signal, 'sent' => $sent, 'failed' => $failed];
        if ($failed) {
            $result->exitCode = 1;
            $result->message = 'failed to signal Task worker PIDs: ' . implode(', ', $failed);
        } else {
            $signalName = $signal === SIGKILL ? 'SIGKILL' : 'SIGTERM';
            $result->message = $sent ? "sent {$signalName} to Task worker PIDs: " . implode(', ', $sent) : 'no Task workers available to reload';
        }
        return $result;
    }
}
