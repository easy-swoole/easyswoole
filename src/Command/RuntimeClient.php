<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\Bridge;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;

class RuntimeClient
{
    public function call(string $action, array $arguments = []): Package
    {
        return $this->run(static fn () => Bridge::bridgeCall($action, $arguments));
    }
    public function run(callable $callback): mixed
    {
        if (Coroutine::getCid() >= 0) { return $callback(); }
        $value = null; $error = null;
        $scheduler = new Scheduler();
        $scheduler->add(static function () use ($callback, &$value, &$error): void {
            try { $value = $callback(); } catch (\Throwable $exception) { $error = $exception; }
        });
        if (!$scheduler->start()) { throw new \RuntimeException('cannot start command coroutine scheduler'); }
        if ($error !== null) { throw $error; }
        return $value;
    }
}
