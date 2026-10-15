<?php

namespace EasySwoole\EasySwoole\Command\Console;

/** Owns terminal input, raw mode, and restoration for one interactive session. */
class Terminal
{
    private ?string $savedMode = null;
    private bool $alternate = false;
    private string $error = '';

    public function supported(): bool
    {
        return PHP_OS_FAMILY !== 'Windows' && function_exists('proc_open')
            && stream_isatty(STDIN) && stream_isatty(STDOUT) && getenv('TERM') !== 'dumb';
    }

    public function open(bool $alternate = false): bool
    {
        if ($this->savedMode !== null) { throw new \LogicException('terminal session is already open'); }
        $this->error = '';
        if (!$this->supported()) { $this->error = 'an interactive terminal is required'; return false; }
        $saved = $this->command(['-g']);
        if ($saved === null || $saved === '') { $this->error = 'cannot read terminal mode'; return false; }
        if ($this->command(['-icanon', '-echo', '-isig', '-ixon', 'min', '1', 'time', '0']) === null) {
            $this->command([$saved]);
            $this->error = 'cannot enable keyboard input';
            return false;
        }
        $this->savedMode = $saved;
        $this->alternate = $alternate;
        register_shutdown_function([$this, 'close']);
        echo ($alternate ? "\033[?1049h" : '') . "\033[?25l";
        return true;
    }

    public function close(): void
    {
        if ($this->savedMode !== null) {
            $saved = $this->savedMode;
            $this->savedMode = null;
            try { $this->command([$saved]); }
            finally { echo "\033[?25h" . ($this->alternate ? "\033[?1049l" : ''); }
        }
    }

    public function error(): string { return $this->error; }

    /** @return array{int, int} Rows and columns. */
    public function dimensions(): array
    {
        $size = $this->command(['size']);
        if ($size !== null && preg_match('/^(\d+)\s+(\d+)$/', $size, $values)) {
            return [(int)$values[1] > 5 ? (int)$values[1] : 24, (int)$values[2] > 1 ? (int)$values[2] : 80];
        }
        return [24, 80];
    }

    protected function command(array $arguments): ?string
    {
        $process = @proc_open(array_merge(['stty'], $arguments), [0 => STDIN, 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) { return null; }
        $output = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        return proc_close($process) === 0 ? trim($output) : null;
    }

    public static function readLine(string $prompt): ?string
    {
        echo $prompt;
        $line = fgets(STDIN);
        return $line === false ? null : $line;
    }

    public function waitForInput(float $seconds): bool
    {
        $read = [STDIN]; $write = $except = [];
        $seconds = max(0, $seconds);
        return stream_select($read, $write, $except, (int)$seconds, (int)(($seconds - (int)$seconds) * 1000000)) === 1;
    }

    public function readKey(): ?string
    {
        $char = fgetc(STDIN);
        if ($char === false) { return null; }
        if ($char === "\033") {
            if (!$this->waitForInput(.1)) { return 'cancel'; }
            $prefix = fgetc(STDIN);
            if (($prefix !== '[' && $prefix !== 'O') || !$this->waitForInput(.1)) { return 'cancel'; }
            return match (fgetc(STDIN)) { 'A' => 'up', 'B' => 'down', default => 'ignore' };
        }
        return match ($char) {
            "\r", "\n" => 'enter', ' ' => 'toggle', 'a', 'A' => 'all',
            'q', 'Q', "\003", "\004", "\032" => 'cancel', default => 'ignore',
        };
    }
}
