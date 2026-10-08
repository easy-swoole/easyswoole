<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Support;

use PHPUnit\Framework\TestCase;

abstract class FilesystemTestCase extends TestCase
{
    protected string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/easyswoole-unit-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->directory);
    }

    protected function fixture(string $name, string $content): string
    {
        $path = $this->directory . '/' . $name;
        file_put_contents($path, $content);
        return $path;
    }
}
