<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

/** 主框架的进程控制服务，不依赖命令行解析或终端渲染。 */
class ServerControlService
{
    public function stop(string $pidFile, bool $force = false, float $timeout = 15): int
    {
        $pid = $this->readPid($pidFile);
        if (!$this->signal($pid, 0)) {
            $this->removePidFile($pidFile, $pid);
            throw new \RuntimeException("pid :{$pid} not exist");
        }
        if (!$this->signal($pid, $force ? SIGKILL : SIGTERM)) {
            throw new \RuntimeException("cannot send stop signal to pid:{$pid}");
        }
        $deadline = $this->now() + $timeout;
        do {
            if (!$this->signal($pid, 0)) {
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
        if (!$this->signal($pid, 0)) { throw new \RuntimeException("pid :{$pid} not exist"); }
        if (!$this->signal($pid, SIGUSR1)) { throw new \RuntimeException("cannot send reload signal to pid:{$pid}"); }
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

    protected function signal(int $pid, int $signal): bool { return \Swoole\Process::kill($pid, $signal); }
    protected function now(): float { return hrtime(true) / 1e9; }
    protected function pause(): void { usleep(50000); }
}
