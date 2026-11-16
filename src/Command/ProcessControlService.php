<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Console\Selection\Choice;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Command\Selector\ProcessSelector;
class ProcessControlService
{
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
            $group = preg_replace('/[\x00-\x1f\x7f]/', '?', (string) $group);
            $lines[] = '';
            $lines[] = $group . ' (' . count($members) . ')';
            foreach ($members as $process) {
                $lines[] = '  [' . ++$index . '] ' . ProcessSelector::label($process);
                $lines[] = '      ' . ProcessSelector::details($process);
                $peak = Utility::formatMemory($process['memoryPeakUsage'] ?? null);
                $heartbeat = Utility::formatTime($process['lastHeartBeat'] ?? null);
                $type = empty($process['customProcess']) ? 'Worker process' : 'Custom process';
                $lines[] = "      Peak {$peak}   lastHeartBeat {$heartbeat}   {$type}";
            }
        }
        return implode(PHP_EOL, $lines);
    }
    protected function fetchProcessList(CommandResult $result): ?array
    {
        $package = $this->runtime->call('processInfo');
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            $result->exitCode = 1;
            $result->message = $package->getMsg() ?? 'cannot query process list';
            return null;
        }
        $rows = $package->getArgs();
        if (!is_array($rows)) {
            throw new \UnexpectedValueException('invalid process list returned by Bridge');
        }
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['pid'], $row['name']) || !is_int($row['pid']) || $row['pid'] <= 0
                || !is_string($row['name']) || (isset($row['group']) && !is_string($row['group']))) {
                throw new \UnexpectedValueException('invalid process list returned by Bridge');
            }
        }
        return $rows;
    }
    protected function selectProcesses(array $choices): array
    {
        $items = array_map(static fn($row) => new Choice($row['pid'], ProcessSelector::label($row), $row, ProcessSelector::details($row)), $choices);
        return $this->console->select('Select processes to terminate:', $items, multiple: true)->values();
    }
    protected \EasySwoole\Command\Console\Console $console;
    public function __construct(private ?RuntimeClient $runtime = null) { $this->runtime ??= new RuntimeClient(); }
    protected function isInteractive(): bool { return $this->console->interactive(); }
    protected function readInput(string $prompt): ?string { return $this->console->prompt($prompt); }
    public function listing(): CommandResult
    {
        $result = new CommandResult();
        $rows = $this->fetchProcessList($result);
        if ($rows !== null) { $result->data = $rows; $result->message = $this->formatProcessList($rows); }
        return $result;
    }
    public function terminate(ProcessSelection $selection, \EasySwoole\Command\Console\Console $console): CommandResult
    {
        $this->console = $console;
        $result = new CommandResult();
        $allProcess = $this->fetchProcessList($result);
        if ($allProcess === null) {
            return $result;
        }
        $force = $selection->force;
        $signal = $force ? SIGKILL : SIGTERM;
        $hasSelector = $selection->explicit();
        $list = [];
        if ($hasSelector) {
            // 显式选择条件保持原有的非交互脚本行为。
            foreach ($allProcess as $process) {
                if ($selection->matches($process)) {
                    $list[$process['pid']] = $process;
                }
            }
        } else {
            if (!$allProcess) {
                $result->data = [];
                $result->message = 'no processes available';
                return $result;
            }
            if (!$this->isInteractive()) {
                $result->exitCode = 1;
                $result->message = 'interactive selection requires a terminal; pass --pid, --group or --name';
                return $result;
            }
            $choices = array_values($allProcess);
            $selected = $this->selectProcesses($choices);
            if (!$selected) {
                $result->data = [];
                $result->message = 'process selection cancelled';
                return $result;
            }
            $signalName = $force ? 'SIGKILL' : 'SIGTERM';
            $targets = implode(', ', array_map(static fn(array $process) => ProcessSelector::label($process), $selected));
            $answer = $this->readInput("Send {$signalName} to {$targets}? [y/N]: ");
            if (!in_array(strtolower(trim($answer ?? '')), ['y', 'yes'], true)) {
                $result->data = [];
                $result->message = 'process selection cancelled';
                return $result;
            }
            // 用户确认期间，所选 PID 可能已被其他进程复用。
            $current = $this->fetchProcessList($result);
            if ($current === null) {
                return $result;
            }
            foreach ($selected as $target) {
                $matched = false;
                foreach ($current as $process) {
                    if ($process['pid'] === $target['pid'] && $process['name'] === $target['name'] && $process['group'] === $target['group'] && $process['startUpTime'] === $target['startUpTime']) {
                        $list[$process['pid']] = $process;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $result->exitCode = 1;
                    $result->message = 'selected process has exited or changed; run process kill again';
                    return $result;
                }
            }
        }
        if (!$list) {
            $result->data = [];
            $result->message = 'No processes matched the specified filters.';
            return $result;
        }
        foreach ($list as $pid => &$process) {
            $sent = $pid > 0 && $this->signalProcess($pid, $signal);
            $process['option'] = $force ? 'SIGKILL' : 'SIGTERM';
            $process['result'] = $sent ? 'signal sent' : 'signal failed';
            if (!$sent) {
                $result->exitCode = 1;
            }
            $process['startUpTime'] = date('Y-m-d H:i:s', $process['startUpTime']);
            unset($process['memoryUsage'], $process['memoryPeakUsage'], $process['lastHeartBeat']);
        }
        unset($process);
        $result->data = $list;
        $result->message = implode(PHP_EOL, array_map(static fn(array $process) => "[{$process['option']}] " . ProcessSelector::label($process) . ': ' . $process['result'], $list));
        return $result;
    }
}
