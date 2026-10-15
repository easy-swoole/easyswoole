<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Crontab\Config as CrontabConfig;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Trigger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class CrontabTest extends TestCase
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
        $cron = new Crontab();
        self::assertSame('/tmp', $cron->getConfig()->getTempDir());
        self::assertSame("CustomApp.{$mode}", $cron->getConfig()->getServerName());
        self::assertSame(0, $cron->schedulerTable()->count());
    }

    public function testProvidedConfigIsRetainedAndFrameworkSettingsAreApplied(): void
    {
        $config = new CrontabConfig();
        $config->setTempDir('/unused');
        $config->setServerName('unused');
        $config->setOnException(static function (): void { throw new \LogicException('Old handler'); });
        $cron = new Crontab($config);
        self::assertSame($config, $cron->getConfig());
        self::assertSame('/tmp', $config->getTempDir());
        self::assertSame('CustomApp.dev', $config->getServerName());
        self::assertIsCallable($config->getOnException());
    }

    public function testCrontabExceptionIsForwardedToTrigger(): void
    {
        $exception = new \RuntimeException('Crontab failed');
        $trigger = $this->createMock(Trigger::class);
        $trigger->expects(self::once())->method('throwable')->with(self::identicalTo($exception));
        (new \ReflectionProperty(Trigger::class, 'instance'))->setValue(null, $trigger);
        $callback = (new Crontab())->getConfig()->getOnException();
        self::assertIsCallable($callback);
        $callback($exception);
    }
}
