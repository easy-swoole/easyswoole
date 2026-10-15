<?php

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\EasySwoole\Command\TaskReloader;
use EasySwoole\EasySwoole\Bridge\Bridge;
use Swoole\Coroutine\Scheduler;
use EasySwoole\EasySwoole\Command\Console\Watch;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Command\Utility;

class Task extends AbstractCommand
{
    public function name(): string { return 'task'; }
    public function description(): string { return 'EasySwoole Task worker manager'; }
    public function beforeExecute(Caller $caller, Result $result): bool
    {
        Core::getInstance()->initialize($caller->commandLine->getOption('mode'));
        return true;
    }
    protected function init(): void
    {
        $action = new Action('show', 'show custom Task worker status');
        $action->addOption(new Option('mode', 'run mode, such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result): void {
            $scheduler = new Scheduler();
            $scheduler->add(function () use ($result): void {
                try {
                    $package = $this->requestStatus();
                    if ($package->getStatus() !== StatusEnum::SUCCESS) {
                        throw new \RuntimeException($package->getMsg() ?? 'cannot query Task status');
                    }
                    $rows = $package->getArgs();
                    if (!is_array($rows)) { throw new \UnexpectedValueException('invalid Task status'); }
                    foreach ($rows as $row) {
                        foreach (['pid', 'accepting', 'running', 'success', 'fail'] as $field) {
                            if (!is_array($row) || !isset($row[$field]) || !is_int($row[$field]) || $row[$field] < 0) {
                                throw new \UnexpectedValueException('invalid Task status');
                            }
                        }
                        if ($row['pid'] === 0 || !in_array($row['accepting'], [0, 1], true)) {
                            throw new \UnexpectedValueException('invalid Task status');
                        }
                    }
                    $result->result = $rows;
                    $lines = ['Task workers (total: ' . count($rows) . ')'];
                    if (!$rows) { $lines[] = '  No Task workers available.'; }
                    foreach ($rows as $index => $row) {
                        $state = empty($row['accepting']) ? 'draining' : 'accepting';
                        $lines[] = '';
                        $lines[] = "[{$index}] PID {$row['pid']}   State {$state}";
                        $lines[] = "    Running {$row['running']}   Success {$row['success']}   Fail {$row['fail']}";
                        $lines[] = '    processUpTime ' . Utility::formatTime($row['startUpTime'] ?? null);
                    }
                    $result->msg = implode(PHP_EOL, $lines);
                } catch (\Throwable $throwable) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = $throwable->getMessage();
                }
            });
            $scheduler->start();
        });
        Watch::attach($action);
        $this->registerAction($action);

        $action = new Action('reload', 'reload EasySwoole custom Task workers');
        $action->addOption(new Option('mode', 'run mode, such as --mode=dev'));
        $action->addOption(new class('force', 'use SIGKILL with --force=true; default SIGTERM') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                    ? true : 'force must be a boolean, such as --force=true or --force=false';
            }
        });
        $action->addOption(new class('rolling', 'restart one Task Worker at a time') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                    ? true : 'rolling must be a boolean';
            }
        });
        $action->addOption(new class('timeout', 'per-worker rolling restart timeout in seconds; default 30') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || (is_numeric($value) && is_finite((float)$value) && (float)$value > 0)
                    ? true : 'timeout must be a positive number of seconds';
            }
        });
        $action->setCallback(function (Caller $caller, Result $result): void {
            $force = $caller->commandLine->hasOption('force')
                && ($caller->commandLine->getOption('force') === null
                    || filter_var($caller->commandLine->getOption('force'), FILTER_VALIDATE_BOOLEAN));
            $signal = $force ? SIGKILL : SIGTERM;
            $rolling = $caller->commandLine->hasOption('rolling')
                && ($caller->commandLine->getOption('rolling') === null || filter_var($caller->commandLine->getOption('rolling'), FILTER_VALIDATE_BOOLEAN));
            $scheduler = new Scheduler();
            $scheduler->add(function () use ($result, $signal, $rolling, $caller): void {
                if ($rolling) {
                    $reloader = $this->taskReloader();
                    try {
                        $rows = $reloader->reload($signal, (float)($caller->commandLine->getOption('timeout') ?? 30));
                        $result->result = $rows;
                        $messages = [];
                        foreach ($rows as $index => $row) {
                            $messages[] = "Task Worker {$index}: PID {$row['oldPid']} -> {$row['pid']} ready";
                        }
                        $result->msg = $messages ? implode(PHP_EOL, $messages) : 'no Task workers available to reload';
                    } catch (\Throwable $throwable) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->result = $reloader->completed();
                        $result->msg = $throwable->getMessage();
                    }
                    return;
                }

                try {
                    $package = Bridge::bridgeCall('taskWorkerPidList');
                    if ($package->getStatus() !== StatusEnum::SUCCESS) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = $package->getMsg();
                        return;
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
                    $result->result = ['signal' => $signal, 'sent' => $sent, 'failed' => $failed];
                    if ($failed) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = 'failed to signal Task worker PIDs: ' . implode(', ', $failed);
                    } else {
                        $signalName = $signal === SIGKILL ? 'SIGKILL' : 'SIGTERM';
                        $result->msg = $sent
                            ? "sent {$signalName} to Task worker PIDs: " . implode(', ', $sent)
                            : 'no Task workers available to reload';
                    }
                } catch (\Throwable $throwable) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = $throwable->getMessage();
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);

    }
    protected function taskReloader(): TaskReloader { return new TaskReloader(); }

    protected function signalTaskWorker(int $pid, int $signal): bool
    {
        return \Swoole\Process::kill($pid, $signal);
    }

    protected function requestStatus(): Package { return Bridge::bridgeCall('taskStatus'); }
}
