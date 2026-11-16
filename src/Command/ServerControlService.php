<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

/** 主框架的进程控制服务，不依赖命令行解析或终端渲染。 */
class ServerControlService
{
    private const ESRCH = 3;

    public function stop(string $pidFile, bool $force = false, float $timeout = 15): int
    {
        $pid = $this->readPid($pidFile);
        if (!$this->processExists($pid)) {
            $this->removePidFile($pidFile, $pid);
            throw new \RuntimeException("pid :{$pid} not exist");
        }
        if (!$this->signal($pid, $force ? SIGKILL : SIGTERM)) {
            throw new \RuntimeException("cannot send stop signal to PID {$pid}: " . swoole_strerror($this->signalError()));
        }
        $deadline = $this->now() + $timeout;
        do {
            if (!$this->processExists($pid)) {
                $this->removePidFile($pidFile, $pid);
                return $pid;
            }
            if ($this->now() >= $deadline) {
                throw new \RuntimeException("stop server fail for pid:{$pid}, try [php easyswoole.php server stop -force] again");
            }
            $this->pause();
        } while (true);
    }

    public function reload(string $pidFile): int
    {
        $pid = $this->readPid($pidFile);
        if (!$this->processExists($pid)) { throw new \RuntimeException("pid :{$pid} not exist"); }
        if (!$this->signal($pid, SIGUSR1)) {
            throw new \RuntimeException("cannot send reload signal to PID {$pid}: " . swoole_strerror($this->signalError()));
        }
        return $pid;
    }

    protected function readPid(string $file): int
    {
        if (!is_file($file)) { throw new \RuntimeException('pid file does not exist, please check whether to run in the daemon mode!'); }
        $value = trim((string)file_get_contents($file));
        $pid = filter_var($value, FILTER_VALIDATE_INT);
        if ($pid === false || $pid <= 0) { throw new \RuntimeException('invalid server PID file'); }
        return $pid;
    }

    protected function removePidFile(string $file, int $pid): void
    {
        // 避免删除新启动服务的 PID 文件。
        if (is_file($file) && trim((string)file_get_contents($file)) === (string)$pid) { unlink($file); }
    }

    private function processExists(int $pid): bool
    {
        if ($this->signal($pid, 0)) {
            return true;
        }
        $error = $this->signalError();
        // 只有 ESRCH 确认进程不存在，权限错误等其他情况必须保留 PID 文件。
        if ($error === self::ESRCH) {
            return false;
        }
        throw new \RuntimeException("cannot check server PID {$pid}: " . swoole_strerror($error));
    }

    protected function signal(int $pid, int $signal): bool { return \Swoole\Process::kill($pid, $signal); }
    protected function signalError(): int { return swoole_errno(); }
    protected function now(): float { return hrtime(true) / 1e9; }
    protected function pause(): void { usleep(50000); }
}
