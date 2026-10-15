<?php

namespace EasySwoole\EasySwoole\Command\Selector;

/** Arrow-key multi-selection for a real terminal; null requests numbered fallback. */
class ProcessSelector
{
    private int $columns = 80;
    private int $viewportSize = 9;
    public function select(array $choices): ?array
    {
        if (!$this->supportsTerminal()) {
            return null;
        }
        $savedMode = $this->terminalMode(['-g']);
        if ($savedMode === null || $savedMode === '') {
            return null;
        }
        if ($this->terminalMode(['-icanon', '-echo', '-isig', '-ixon', 'min', '1', 'time', '0']) === null) {
            $this->terminalMode([$savedMode]);
            return null;
        }
        $size = $this->terminalMode(['size']);
        if ($size !== null && preg_match('/^(\d+)\s+(\d+)$/', $size, $dimensions)) {
            if ((int)$dimensions[1] > 5) {
                $this->viewportSize = min(10, max(1, intdiv((int)$dimensions[1] - 5, 2)));
            }
            if ((int)$dimensions[2] > 1) {
                $this->columns = (int)$dimensions[2];
            }
        }
        $active = true;
        $restore = function () use ($savedMode, &$active): void {
            if ($active) {
                $this->terminalMode([$savedMode]);
                echo "\033[?25h";
                $active = false;
            }
        };
        register_shutdown_function($restore);
        $cursor = 0;
        $selected = [];
        $lines = 0;
        echo "\033[?25l";
        try {
            while (true) {
                $lines = $this->render($choices, $cursor, $selected, $lines);
                $key = $this->readKey();
                if ($key === null || $key === 'cancel') {
                    return [];
                }
                if ($key === 'up' || $key === 'down') {
                    $cursor = ($cursor + ($key === 'up' ? -1 : 1) + count($choices) + 1) % (count($choices) + 1);
                } elseif ($key === 'toggle' && $cursor < count($choices)) {
                    if (isset($selected[$cursor])) {
                        unset($selected[$cursor]);
                    } else {
                        $selected[$cursor] = true;
                    }
                } elseif ($key === 'all') {
                    $selected = count($selected) === count($choices) ? [] : array_fill_keys(array_keys($choices), true);
                } elseif ($key === 'enter') {
                    if ($cursor === count($choices)) {
                        return [];
                    }
                    if (!$selected) {
                        $selected[$cursor] = true;
                    }
                    ksort($selected);
                    return array_intersect_key($choices, $selected);
                }
            }
        } finally {
            $restore();
        }
    }

    protected function supportsTerminal(): bool
    {
        return PHP_OS_FAMILY !== 'Windows' && function_exists('proc_open')
            && stream_isatty(STDIN) && stream_isatty(STDOUT) && getenv('TERM') !== 'dumb';
    }

    protected function terminalMode(array $arguments): ?string
    {
        $process = @proc_open(array_merge(['stty'], $arguments), [0 => STDIN, 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            return null;
        }
        $output = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return proc_close($process) === 0 ? trim($output) : null;
    }

    protected function readKey(): ?string
    {
        $char = fgetc(STDIN);
        if ($char === false) {
            return null;
        }
        if ($char === "\033") {
            $read = [STDIN];
            $write = $except = [];
            if (stream_select($read, $write, $except, 0, 100000) !== 1) {
                return 'cancel';
            }
            $prefix = fgetc(STDIN);
            if ($prefix !== '[' && $prefix !== 'O') {
                return 'cancel';
            }
            $read = [STDIN];
            if (stream_select($read, $write, $except, 0, 100000) !== 1) {
                return 'cancel';
            }
            return match (fgetc(STDIN)) { 'A' => 'up', 'B' => 'down', default => 'ignore' };
        }
        return match ($char) {
            "\r", "\n" => 'enter',
            ' ' => 'toggle',
            'a', 'A' => 'all',
            'q', 'Q', "\003", "\004", "\032" => 'cancel',
            default => 'ignore',
        };
    }

    protected function title(): string
    {
        return 'Select processes to terminate:';
    }

    protected function render(array $choices, int $cursor, array $selected, int $previousLines): int
    {
        if ($previousLines) {
            echo "\033[{$previousLines}A\r";
        }
        // Limit the viewport so long process lists still fit in an ordinary terminal.
        $start = max(0, min($cursor - intdiv($this->viewportSize, 2), count($choices) - $this->viewportSize));
        $frame = [$this->title(), '↑/↓ Move  Space Toggle  a All/None  Enter Continue  q/Esc Cancel'];
        foreach (array_slice($choices, $start, $this->viewportSize, true) as $index => $process) {
            $focus = $cursor === $index ? '>' : ' ';
            $mark = isset($selected[$index]) ? 'x' : ' ';
            $frame[] = "{$focus} [{$mark}] [" . ($index + 1) . '] ' . static::label($process);
            $frame[] = '        ' . static::details($process);
        }
        $frame[] = ($cursor === count($choices) ? '>' : ' ') . ' [0] Cancel';
        $frame[] = 'Selected: ' . count($selected) . ' | Enter with none checked selects the current item.';
        $lineCount = max($previousLines, count($frame));
        foreach (array_pad($frame, $lineCount, '') as $line) {
            echo "\r\033[2K" . $this->fitLine($line) . "\n";
        }
        return $lineCount;
    }

    private function fitLine(string $line): string
    {
        $width = $this->columns - 1;
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($line, 0, $width, '', 'UTF-8');
        }
        // Conservatively count non-ASCII characters as two cells without mbstring.
        $result = '';
        foreach (preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $character) {
            $width -= strlen($character) > 1 ? 2 : 1;
            if ($width < 0) {
                break;
            }
            $result .= $character;
        }
        return $result;
    }

    public static function details(array $process): string
    {
        $memory = self::formatMemory($process['memoryUsage'] ?? null);
        $started = self::formatTime($process['startUpTime'] ?? null);
        return "Memory {$memory}   processUpTime {$started}";
    }

    public static function formatMemory(mixed $bytes): string
    {
        if ((!is_int($bytes) && !is_float($bytes)) || !is_finite((float)$bytes) || $bytes < 0) {
            return '-';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $unit = 0;
        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            ++$unit;
        }
        return rtrim(rtrim(number_format($bytes, 2, '.', ''), '0'), '.') . ' ' . $units[$unit];
    }

    public static function formatTime(mixed $timestamp): string
    {
        return is_int($timestamp) && $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : '-';
    }

    public static function label(array $process): string
    {
        // Do not interpret control characters from a configured process name as terminal escapes.
        $name = preg_replace('/[\x00-\x1f\x7f]/', '?', $process['name']);
        return "{$name}   PID {$process['pid']}";
    }
}
