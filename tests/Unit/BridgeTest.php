<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Bridge as BridgeServer;
use EasySwoole\Bridge\BridgeProcess;
use EasySwoole\EasySwoole\Bridge\BaseService;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class BridgeTest extends TestCase
{
    protected function setUp(): void
    {
        // Avoid application bootstrap and filesystem changes; no server is created.
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
    public function testProcessConfigurationAndSocketUseApplicationAndMode(string $mode): void
    {
        Core::getInstance()->runMode($mode);
        $bridge = new Bridge();
        self::assertSame("CustomApp.{$mode}", $bridge->getConfig()->getServerName());
        $path = (new \ReflectionMethod(BridgeServer::class, 'getSocketFile'))->invoke($bridge);
        self::assertSame("/tmp/CustomApp.{$mode}.bridge.sock", $path);
        self::assertInstanceOf(BaseService::class, $bridge->getCommandContainer()->get('BaseService'));
    }

    public function testBridgeProcessExceptionIsForwardedToTriggerWithoutEscaping(): void
    {
        $exception = new \RuntimeException('command execution failed');
        $trigger = $this->createMock(Trigger::class);
        $trigger->expects(self::once())->method('throwable')->with(self::identicalTo($exception));
        (new \ReflectionProperty(Trigger::class, 'instance'))->setValue(null, $trigger);

        $bridge = new Bridge();
        $callback = (new \ReflectionProperty(BridgeServer::class, 'onException'))->getValue($bridge);
        self::assertIsCallable($callback);

        $process = (new \ReflectionClass(BridgeProcess::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(BridgeProcess::class, 'onException'))->setValue($process, $callback);
        (new \ReflectionMethod(BridgeProcess::class, 'onException'))->invoke($process, $exception);
    }
}
