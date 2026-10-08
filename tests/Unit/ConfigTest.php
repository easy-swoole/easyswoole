<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Config\SplArrayConfig;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Test\Support\FilesystemTestCase;

final class ConfigTest extends FilesystemTestCase
{
    public function testNestedValuesAndMissingKeys(): void
    {
        $config = new Config();
        self::assertSame([], $config->getConf());
        self::assertTrue($config->setConf('server.port', 9501));
        self::assertSame(9501, $config->getConf('server.port'));
        self::assertNull($config->getConf('missing'));
        self::assertSame(['server' => ['port' => 9501]], $config->toArray());
    }

    public function testMergePreservesOtherKeysAndLoadReplacesThem(): void
    {
        $config = new Config();
        $config->load(['server' => ['port' => 9501, 'host' => 'localhost'], 'keep' => true]);
        self::assertTrue($config->merge(['server' => ['port' => 9502]]));
        self::assertSame(['port' => 9502], $config->getConf('server'));
        self::assertTrue($config->getConf('keep'));
        self::assertTrue($config->load(['fresh' => true]));
        self::assertNull($config->getConf('server'));
        self::assertTrue($config->clear());
        self::assertSame([], $config->toArray());
    }

    public function testStorageHandlerCanBeReplaced(): void
    {
        $first = new SplArrayConfig();
        $config = new Config($first);
        self::assertSame($first, $config->storageHandler());
        $second = new SplArrayConfig();
        $second->load(['source' => 'second']);
        self::assertSame($second, $config->storageHandler($second));
        self::assertSame('second', $config->getConf('source'));
    }

    public function testFileMergeAndReplacement(): void
    {
        $config = new Config();
        $config->load(['keep' => true]);
        self::assertTrue($config->loadFile($this->fixture('merge.php', '<?php return ["port" => 9501];')));
        self::assertTrue($config->getConf('keep'));
        self::assertTrue($config->loadFile($this->fixture('replace.php', '<?php return ["port" => 9502];'), false));
        self::assertSame(['port' => 9502], $config->toArray());
    }

    public function testInvalidFilesDoNotChangeConfiguration(): void
    {
        $config = new Config();
        $config->load(['keep' => true]);
        self::assertFalse($config->loadFile($this->directory . '/missing.php'));
        self::assertFalse($config->loadFile($this->fixture('invalid.php', '<?php return "invalid";')));
        self::assertFalse($config->loadEnv($this->directory . '/missing.ini'));
        self::assertFalse($config->loadDir($this->directory . '/missing'));
        self::assertSame(['keep' => true], $config->toArray());
    }

    public function testDirectoryLoadsNestedConfigurationFiles(): void
    {
        mkdir($this->directory . '/nested');
        $this->fixture('first.php', '<?php return ["first" => 1];');
        $this->fixture('nested/second.php', '<?php return ["second" => 2];');
        $config = new Config();
        self::assertTrue($config->loadDir($this->directory));
        self::assertSame(1, $config->getConf('first'));
        self::assertSame(2, $config->getConf('second'));
    }

    public function testIniSectionsMergeAndReplace(): void
    {
        $path = $this->fixture('env.ini', "[database]\nhost=localhost\nport=3306\n");
        $config = new Config();
        $config->load(['keep' => true]);
        self::assertTrue($config->loadEnv($path));
        self::assertSame('localhost', $config->getConf('database.host'));
        self::assertSame('3306', $config->getConf('database.port'));
        self::assertTrue($config->getConf('keep'));
        self::assertTrue($config->loadEnv($path, false));
        self::assertNull($config->getConf('keep'));
    }

    public function testRepeatedFileLoadCurrentlyReturnsFalse(): void
    {
        $path = $this->fixture('once.php', '<?php return ["enabled" => true];');
        $config = new Config();
        self::assertTrue($config->loadFile($path));
        // Characterization: require_once returns true rather than the array on the second call.
        self::assertFalse($config->loadFile($path));
        self::assertTrue($config->getConf('enabled'));
    }
}
