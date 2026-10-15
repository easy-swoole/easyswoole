<?php

namespace EasySwoole\EasySwoole\Command\Console;

use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;

class Watch
{
    protected Terminal $terminal;

    public function __construct(?Terminal $terminal = null)
    {
        $this->terminal = $terminal ?? new Terminal();
    }

    public static function attach(Action $action): void
    {
        $action->addOption(new class('watch', 'refresh interval in seconds, such as --watch=2; q to exit') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || (is_numeric($value) && is_finite((float)$value) && (float)$value > 0)
                    ? true : 'watch must be a positive number of seconds';
            }
        });
        $callback = $action->getCallback();
        $action->setCallback(static function (Caller $caller, Result $result) use ($callback): void {
            if (!$caller->commandLine->hasOption('watch')) {
                $callback($caller, $result);
                return;
            }
            (new self())->run($callback, $caller, $result, (float)($caller->commandLine->getOption('watch') ?? 2));
        });
    }

    public function run(callable $callback, Caller $caller, Result $result, float $interval): void
    {
        if (!is_finite($interval) || $interval <= 0) {
            $result->status = ExecStatusEnum::COMMAND_ACTION_OPTION_VALIDATE_FAIL;
            $result->msg = 'watch must be a positive number of seconds';
            return;
        }
        if (!$this->terminal->supported()) {
            $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
            $result->msg = '--watch requires an interactive terminal';
            return;
        }
        if (!$this->terminal->open(true)) {
            $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
            $result->msg = $this->terminal->error() . ' for --watch';
            return;
        }
        try {
            do {
                $snapshot = new Result();
                $snapshot->status = ExecStatusEnum::OK;
                $callback($caller, $snapshot);
                $result->status = $snapshot->status;
                $result->result = $snapshot->result;
                $result->msg = $snapshot->msg;
                if ($result->status !== ExecStatusEnum::OK) { return; }
                $this->renderFrame($result->msg ?? '', $interval);
            } while ($this->waitForRefresh($interval));
        } catch (\Throwable $throwable) {
            $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
            $result->msg = $throwable->getMessage();
        } finally {
            $this->terminal->close();
        }
    }

    protected function renderFrame(string $message, float $interval): void
    {
        echo "\033[H\033[JRefreshing every {$interval}s | q/Esc/Ctrl+C to exit\n\n{$message}\n";
    }

    protected function waitForRefresh(float $interval): bool
    {
        $deadline = hrtime(true) / 1e9 + $interval;
        while (($remaining = $deadline - hrtime(true) / 1e9) > 0) {
            if ($this->terminal->waitForInput(min($remaining, .1))) {
                $key = $this->terminal->readKey();
                if ($key === null || $key === 'cancel') { return false; }
            }
        }
        return true;
    }
}
