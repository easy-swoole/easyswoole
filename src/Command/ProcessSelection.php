<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

/** 显式进程选择条件沿用原有命令行的 OR 匹配语义。 */
final class ProcessSelection
{
    public function __construct(
        public readonly ?int $pid = null,
        public readonly ?string $group = null,
        public readonly ?string $name = null,
        public readonly bool $force = false,
    ) {
        if ($pid !== null && $pid <= 0) { throw new \InvalidArgumentException('pid must be positive'); }
        foreach ([$group, $name] as $value) {
            if ($value !== null && trim($value) === '') { throw new \InvalidArgumentException('process group/name cannot be empty'); }
        }
    }

    public function explicit(): bool { return $this->pid !== null || $this->group !== null || $this->name !== null; }

    public function matches(array $process): bool
    {
        return ($this->pid !== null && $process['pid'] === $this->pid)
            || ($this->group !== null && ($process['group'] ?? null) === $this->group)
            || ($this->name !== null && ($process['name'] ?? null) === $this->name);
    }
}
