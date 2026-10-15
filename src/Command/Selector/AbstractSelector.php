<?php

namespace EasySwoole\EasySwoole\Command\Selector;

use EasySwoole\EasySwoole\Command\Console\Terminal;

abstract class AbstractSelector
{
    private int $columns = 80;
    private int $viewportSize = 9;
    protected Terminal $terminal;

    public function __construct(?Terminal $terminal = null)
    {
        $this->terminal = $terminal ?? new Terminal();
    }

    abstract protected function title(): string;
    abstract public static function label(array $choice): string;
    abstract public static function details(array $choice): string;

    /** Numbered fallback and arrow-key selection share the same domain renderer. */
    public function choose(array $choices, ?callable $readInput = null): array
    {
        $choices = array_values($choices);
        if (!$choices) { return []; }
        $selected = $this->select($choices);
        if ($selected !== null) { return $selected; }
        $readInput ??= [Terminal::class, 'readLine'];
        echo $this->title() . "\n";
        foreach ($choices as $index => $choice) {
            echo '[' . ($index + 1) . '] ' . static::label($choice) . "\n";
            echo '    ' . static::details($choice) . "\n";
        }
        echo "[0] Cancel\n";
        while (true) {
            $input = trim($readInput('Enter indexes (comma-separated) [0]: ') ?? '');
            if ($input === '' || $input === '0' || strtolower($input) === 'q') { return []; }
            $selected = [];
            foreach (preg_split('/[\s,，]+/u', $input) as $index) {
                if (!ctype_digit($index) || (int)$index < 1 || (int)$index > count($choices)) {
                    $selected = [];
                    break;
                }
                $selected[(int)$index - 1] = $choices[(int)$index - 1];
            }
            if ($selected) { return $selected; }
            echo "Invalid selection; enter listed indexes or 0 to cancel.\n";
        }
    }

    /** Null means raw terminal input is unavailable; [] means cancellation. */
    public function select(array $choices): ?array
    {
        $choices = array_values($choices);
        if (!$choices) { return []; }
        if (!$this->terminal->open()) { return null; }
        try {
            [$rows, $this->columns] = $this->terminal->dimensions();
            $this->viewportSize = min(10, max(1, intdiv($rows - 5, 2)));
        } catch (\Throwable $error) {
            $this->terminal->close();
            throw $error;
        }
        $cursor = 0;
        $selected = [];
        $lines = 0;
        try {
            while (true) {
                $lines = $this->render($choices, $cursor, $selected, $lines);
                $key = $this->terminal->readKey();
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
            $this->terminal->close();
        }
    }

    protected function render(array $choices, int $cursor, array $selected, int $previousLines): int
    {
        if ($previousLines) {
            echo "\033[{$previousLines}A\r";
        }
        // Limit the viewport so long process lists still fit in an ordinary terminal.
        $start = max(0, min($cursor - intdiv($this->viewportSize, 2), count($choices) - $this->viewportSize));
        $frame = [$this->title(), '↑/↓ Move  Space Toggle  a All/None  Enter Continue  q/Esc Cancel'];
        foreach (array_slice($choices, $start, $this->viewportSize, true) as $index => $choice) {
            $focus = $cursor === $index ? '>' : ' ';
            $mark = isset($selected[$index]) ? 'x' : ' ';
            $frame[] = "{$focus} [{$mark}] [" . ($index + 1) . '] ' . static::label($choice);
            $frame[] = '        ' . static::details($choice);
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

}
