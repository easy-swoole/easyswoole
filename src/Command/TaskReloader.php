<?php

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Bridge\Bridge;

class TaskReloader
{
    private array $completed = [];

    public function completed(): array { return $this->completed; }

    public function reload(int $signal = SIGTERM, float $timeout = 30): array
    {
        $this->completed = [];
        if (!is_finite($timeout) || $timeout <= 0) {
            throw new \InvalidArgumentException('timeout must be positive');
        }
        if (!in_array($signal, [SIGTERM, SIGKILL], true)) {
            throw new \InvalidArgumentException('rolling reload requires SIGTERM or SIGKILL');
        }
        $initial = $this->status();
        ksort($initial, SORT_NUMERIC);
        foreach ($initial as $index => $target) {
            $workers = $this->status();
            $current = $workers[$index] ?? null;
            if (!$current || $current['pid'] !== $target['pid'] || empty($current['accepting'])) {
                throw new \RuntimeException("Task Worker {$index} changed or is not ready; rolling reload stopped");
            }
            if (count($initial) > 1) {
                $available = array_filter($workers, static fn (array $row) => !empty($row['accepting']) && $row['pid'] !== $current['pid']);
                if (!$available) { throw new \RuntimeException('no other Task Worker is accepting; rolling reload stopped'); }
            }
            $pid = $current['pid'];
            if (!$this->signal($pid, $signal)) {
                throw new \RuntimeException("failed to signal Task Worker {$index} PID {$pid}; rolling reload stopped");
            }
            $deadline = $this->now() + $timeout;
            do {
                $workers = $this->status();
                $replacement = $workers[$index] ?? null;
                if ($replacement && $replacement['pid'] !== $pid && !empty($replacement['accepting']) && !$this->pidExists($pid)) {
                    $this->completed[$index] = ['oldPid' => $pid, 'pid' => $replacement['pid'], 'signal' => $signal];
                    continue 2;
                }
                if ($this->now() >= $deadline) {
                    throw new \RuntimeException("timeout waiting for Task Worker {$index} PID {$pid} to drain and restart; rolling reload stopped");
                }
                $this->pause();
            } while (true);
        }
        return $this->completed;
    }

    protected function status(): array
    {
        $package = Bridge::bridgeCall('taskStatus');
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            throw new \RuntimeException($package->getMsg() ?? 'cannot query Task status');
        }
        $rows = $package->getArgs();
        if (!is_array($rows)) { throw new \UnexpectedValueException('invalid Task status'); }
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['pid']) || !is_int($row['pid']) || $row['pid'] <= 0) {
                throw new \UnexpectedValueException('invalid Task worker PID');
            }
        }
        return $rows;
    }

    protected function signal(int $pid, int $signal): bool { return \Swoole\Process::kill($pid, $signal); }

    protected function pidExists(int $pid): bool
    {
        if (function_exists('posix_kill')) {
            if (posix_kill($pid, 0)) { return true; }
            if (posix_get_last_error() === 3) { return false; }
            throw new \RuntimeException("cannot check Task worker PID {$pid}");
        }
        set_error_handler(static fn () => true);
        try { return \Swoole\Process::kill($pid, 0); }
        finally { restore_error_handler(); }
    }

    protected function now(): float { return hrtime(true) / 1e9; }
    protected function pause(): void { usleep(100000); }
}
