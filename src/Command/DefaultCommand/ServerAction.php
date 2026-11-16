<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkAction;
use EasySwoole\Bridge\Package;

abstract class ServerAction extends FrameworkAction
{
    protected function formatServerStatus(array $data): string
    {
        $lines = ['Server status'];
        foreach (['Environment' => 'env', 'Runtime' => 'runtime'] as $title => $section) {
            $lines[] = '';
            $lines[] = $title;
            $lines[] = $this->formatStatusSection($data[$section], $section === 'env');
        }
        return implode(PHP_EOL, $lines);
    }
    protected function formatStatusSection(array $values, bool $environment): string
    {
        $lines = [];
        if (!$values) {
            $lines[] = '  No data available.';
        }
        $items = [];
        $paths = [];
        $keyWidth = $values ? max(array_map(static fn($key) => strlen((string) $key), array_keys($values))) : 28;
        foreach ($values as $key => $value) {
            if (!$environment && $key === 'start_time') {
                $value = is_int($value) && $value > 0 ? date('Y-m-d H:i:s', $value) : '-';
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '-';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            }
            $key = preg_replace('/[\x00-\x1f\x7f]/', '?', (string) $key);
            $value = preg_replace('/[\x00-\x1f\x7f]/', '?', (string) $value);
            $item = str_pad($key, $keyWidth) . ' : ' . $value;
            if ($environment && in_array($key, ['pid_file', 'log_file', 'temp dir', 'log dir'], true)) {
                $paths[] = $item;
            } else {
                $items[] = $item;
            }
        }
        if ($items) {
            $rows = array_chunk($items, 2);
            $leftWidth = max(array_map(static fn(array $row) => strlen($row[0]), $rows)) + 4;
            foreach ($rows as $row) {
                $lines[] = '  ' . (isset($row[1]) ? str_pad($row[0], $leftWidth) . $row[1] : $row[0]);
            }
        }
        foreach ($paths as $path) {
            $lines[] = '  ' . $path;
        }
        return implode(PHP_EOL, $lines);
    }
    protected function requestServerStatus(): Package
    {
        return $this->runtime->call('serverStatus');
    }
}
