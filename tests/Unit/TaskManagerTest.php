<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Task\Config as TaskConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TaskManagerTest extends TestCase
{
    protected function setUp(): void
    {
        define('EASYSWOOLE_TEMP_DIR', '/tmp');
        $core = (new \ReflectionClass(Core::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Core::class, 'instance'))->setValue(null, $core);
        Config::getInstance()->load(['SERVER_NAME' => 'CustomApp']);
    }

    public static function modes(): iterable
    {
        yield ['dev'];
        yield ['produce'];
    }

    #[DataProvider('modes')]
    public function testConstructorInjectsSettingsWithoutServerCreation(string $mode): void
    {
        Core::getInstance()->runMode($mode);
        Config::getInstance()->setConf('MAIN_SERVER.TASK', [
            'workerNum' => 2, 'maxRunningNum' => 16, 'pushTimeOut' => 1.5,
            'maxPackageSize' => 4096, 'tempDir' => '/unused', 'serverName' => 'unused',
        ]);
        $manager = new TaskManager();
        $config = $manager->getConfig();
        self::assertSame(2, $config->getWorkerNum());
        self::assertSame(16, $config->getMaxRunningNum());
        self::assertSame(1.5, $config->getPushTimeOut());
        self::assertSame(4096, $config->getMaxPackageSize());
        self::assertSame('/tmp', $config->getTempDir());
        self::assertSame("CustomApp.{$mode}", $config->getServerName());
        self::assertSame([], $manager->status());
    }

    public function testMissingTaskSectionKeepsDependencyDefaults(): void
    {
        $defaults = new TaskConfig();
        $manager = new TaskManager();
        self::assertSame($defaults->getWorkerNum(), $manager->getConfig()->getWorkerNum());
        self::assertSame($defaults->getMaxRunningNum(), $manager->getConfig()->getMaxRunningNum());
        self::assertSame('CustomApp.dev', $manager->getConfig()->getServerName());
    }

    public function testProvidedConfigIsRetainedAndFrameworkSettingsAreApplied(): void
    {
        $config = new TaskConfig();
        $config->setMaxPackageSize(8192);
        $config->setWorkerNum(3);
        Config::getInstance()->setConf('MAIN_SERVER.TASK', ['workerNum' => 2]);
        $manager = new TaskManager($config);
        self::assertSame($config, $manager->getConfig());
        self::assertSame(2, $config->getWorkerNum());
        self::assertSame(8192, $config->getMaxPackageSize());
        self::assertSame('/tmp', $config->getTempDir());
    }

    public function testTaskExceptionIsForwardedToTrigger(): void
    {
        $exception = new \RuntimeException('Task failed');
        $trigger = $this->createMock(Trigger::class);
        $trigger->expects(self::once())->method('throwable')->with(self::identicalTo($exception));
        (new \ReflectionProperty(Trigger::class, 'instance'))->setValue(null, $trigger);
        $callback = (new TaskManager())->getConfig()->getOnException();
        self::assertIsCallable($callback);
        $callback($exception);
    }
}
