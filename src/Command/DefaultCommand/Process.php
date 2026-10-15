<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Selector\ProcessSelector;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Scheduler;

class Process extends AbstractCommand
{
    function name(): string
    {
        return 'process';
    }

    function description(): string
    {
        return 'EasySwoole process manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init(): void
    {
        $action = new Action('show','show EasySwoole process list');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result): void {
            $processes = $this->fetchProcessList($result);
            if ($processes !== null) {
                $result->result = $processes;
                $result->msg = $this->formatProcessList($processes);
            }
        });
        $this->registerAction($action);

        $action = new Action('kill', 'kill EasySwoole process');
        $action->addOption(new Option('mode', 'run mode, such as --mode=dev'));
        $action->addOption(new class('pid', 'kill specified pid, such as --pid=9501') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return !$caller->commandLine->hasOption('pid')
                    || (is_string($value) && ctype_digit($value) && (int)$value > 0)
                    ? true : 'pid must be a positive integer';
            }
        });
        foreach (['group', 'name'] as $selector) {
            $action->addOption(new class($selector, 'kill processes with this exact ' . $selector) extends Option {
                public static function validate(mixed $value, Caller $caller): bool|string
                {
                    return $value === null || (is_string($value) && trim($value) !== '')
                        ? true : 'process group/name cannot be empty';
                }
            });
        }
        $action->addOption(new class('force', 'use SIGKILL with --force=true; default SIGTERM') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                    ? true : 'force must be a boolean';
            }
        });
        $action->setCallback(function (Caller $caller, Result $result): void {
            $allProcess = $this->fetchProcessList($result);
            if ($allProcess === null) {
                return;
            }
            $line = $caller->commandLine;
            $force = $line->hasOption('force') && ($line->getOption('force') === null
                || filter_var($line->getOption('force'), FILTER_VALIDATE_BOOLEAN));
            $signal = $force ? SIGKILL : SIGTERM;
            $hasSelector = $line->hasOption('pid') || $line->hasOption('group') || $line->hasOption('name');
            $list = [];
            if ($hasSelector) {
                // Explicit selectors retain the existing non-interactive script behavior.
                foreach ($allProcess as $process) {
                    if (($line->hasOption('pid') && $process['pid'] === (int)$line->getOption('pid'))
                        || ($line->hasOption('group') && $line->getOption('group') !== null && $process['group'] === $line->getOption('group'))
                        || ($line->hasOption('name') && $line->getOption('name') !== null && $process['name'] === $line->getOption('name'))) {
                        $list[$process['pid']] = $process;
                    }
                }
            } else {
                if (!$allProcess) {
                    $result->result = [];
                    $result->msg = 'no processes available';
                    return;
                }
                if (!$this->isInteractive()) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = 'interactive selection requires a terminal; pass --pid, --group or --name';
                    return;
                }
                $choices = array_values($allProcess);
                $selected = $this->selectProcesses($choices);
                if (!$selected) {
                    $result->result = [];
                    $result->msg = 'process selection cancelled';
                    return;
                }
                $signalName = $force ? 'SIGKILL' : 'SIGTERM';
                $targets = implode(', ', array_map(static fn (array $process) => ProcessSelector::label($process), $selected));
                $answer = $this->readInput("Send {$signalName} to {$targets}? [y/N]: ");
                if (!in_array(strtolower(trim($answer ?? '')), ['y', 'yes'], true)) {
                    $result->result = [];
                    $result->msg = 'process selection cancelled';
                    return;
                }
                // The selected PID may have been replaced while the user was deciding.
                $current = $this->fetchProcessList($result);
                if ($current === null) {
                    return;
                }
                foreach ($selected as $target) {
                    $matched = false;
                    foreach ($current as $process) {
                        if ($process['pid'] === $target['pid'] && $process['name'] === $target['name']
                            && $process['group'] === $target['group'] && $process['startUpTime'] === $target['startUpTime']) {
                            $list[$process['pid']] = $process;
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = 'selected process has exited or changed; run process kill again';
                        return;
                    }
                }
            }
            if (!$list) {
                $result->result = [];
                $result->msg = 'not any process match';
                return;
            }
            foreach ($list as $pid => &$process) {
                $sent = $pid > 0 && $this->signalProcess($pid, $signal);
                $process['option'] = $force ? 'SIGKILL' : 'SIGTERM';
                $process['result'] = $sent ? 'signal sent' : 'signal failed';
                if (!$sent) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
                $process['startUpTime'] = date('Y-m-d H:i:s', $process['startUpTime']);
                unset($process['memoryUsage'], $process['memoryPeakUsage'], $process['lastHeartBeat']);
            }
            unset($process);
            $result->result = $list;
            $result->msg = implode(PHP_EOL, array_map(static fn (array $process) =>
                "[{$process['option']}] " . ProcessSelector::label($process) . ': ' . $process['result'], $list));
        });
        $this->registerAction($action);
    }

    protected function selectProcesses(array $choices): array
    {
        $selection = (new ProcessSelector())->select($choices);
        if ($selection !== null) {
            return $selection;
        }
        echo "Select processes to terminate:\n";
        foreach ($choices as $index => $process) {
            echo '[' . ($index + 1) . '] ' . ProcessSelector::label($process) . "\n";
            echo '    ' . ProcessSelector::details($process) . "\n";
        }
        echo "[0] Cancel\n";
        while (true) {
            $input = trim($this->readInput('Enter indexes (comma-separated) [0]: ') ?? '');
            if ($input === '' || $input === '0' || strtolower($input) === 'q') {
                return [];
            }
            $indexes = preg_split('/[\s,，]+/u', $input);
            $selected = [];
            foreach ($indexes as $index) {
                if (!ctype_digit($index) || (int)$index < 1 || (int)$index > count($choices)) {
                    $selected = [];
                    break;
                }
                $selected[(int)$index - 1] = $choices[(int)$index - 1];
            }
            if ($selected) {
                return $selected;
            }
            echo "Invalid selection; enter listed indexes or 0 to cancel.\n";
        }
    }

    protected function fetchProcessList(Result $result): ?array
    {
        $processes = null;
        $scheduler = new Scheduler();
        $scheduler->add(static function () use ($result, &$processes): void {
            try {
                $package = Bridge::bridgeCall('processInfo');
                if ($package->getStatus() !== StatusEnum::SUCCESS) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = $package->getMsg();
                } elseif (!is_array($package->getArgs())) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = 'invalid process list returned by Bridge';
                } else {
                    $processes = $package->getArgs();
                }
            } catch (\Throwable $throwable) {
                $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                $result->msg = $throwable->getMessage();
            }
        });
        $scheduler->start();
        return $processes;
    }

    protected function isInteractive(): bool
    {
        return stream_isatty(STDIN);
    }

    protected function readInput(string $prompt): ?string
    {
        echo $prompt;
        $line = fgets(STDIN);
        return $line === false ? null : $line;
    }

    protected function signalProcess(int $pid, int $signal): bool
    {
        return \Swoole\Process::kill($pid, $signal);
    }

    protected function formatProcessList(array $processes): string
    {
        if (!$processes) {
            return 'No running processes.';
        }
        $groups = [];
        foreach ($processes as $process) {
            $groups[$process['group'] ?? 'Ungrouped'][] = $process;
        }
        $lines = ['Processes (total: ' . count($processes) . ')'];
        $index = 0;
        foreach ($groups as $group => $members) {
            $group = preg_replace('/[\x00-\x1f\x7f]/', '?', (string)$group);
            $lines[] = '';
            $lines[] = $group . ' (' . count($members) . ')';
            foreach ($members as $process) {
                $lines[] = '  [' . (++$index) . '] ' . ProcessSelector::label($process);
                $lines[] = '      ' . ProcessSelector::details($process);
                $peak = ProcessSelector::formatMemory($process['memoryPeakUsage'] ?? null);
                $heartbeat = ProcessSelector::formatTime($process['lastHeartBeat'] ?? null);
                $type = empty($process['customProcess']) ? 'Worker process' : 'Custom process';
                $lines[] = "      Peak {$peak}   lastHeartBeat {$heartbeat}   {$type}";
            }
        }
        return implode(PHP_EOL, $lines);
    }

}
