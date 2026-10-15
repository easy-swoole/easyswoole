<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase
{
    private mixed $previousConfig;
    private mixed $previousServerManager;

    protected function setUp(): void
    {
        $this->previousConfig = (new \ReflectionProperty(Config::class, 'instance'))->getValue();
        $this->previousServerManager = (new \ReflectionProperty(ServerManager::class, 'instance'))->getValue();
        (new \ReflectionProperty(Config::class, 'instance'))->setValue(null, new Config());
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(Config::class, 'instance'))->setValue(null, $this->previousConfig);
        (new \ReflectionProperty(ServerManager::class, 'instance'))->setValue(null, $this->previousServerManager);
    }

    public static function nativeTaskWorkerSettings(): iterable
    {
        yield 'positive worker count' => [4];
        yield 'zero' => [0];
        yield 'negative worker count' => [-1];
        yield 'null' => [null];
        yield 'false' => [false];
        yield 'string count' => ['4'];
    }

    #[DataProvider('nativeTaskWorkerSettings')]
    public function testNativeTaskWorkerSettingIsRejectedBeforeServerCreation(mixed $value): void
    {
        Config::getInstance()->load(['MAIN_SERVER' => [
            'PORT' => 9501,
            'SERVER_TYPE' => 2,
            'LISTEN_ADDRESS' => '127.0.0.1',
            'SETTING' => ['task_worker_num' => $value],
            'RUN_MODEL' => 2,
            'SOCK_TYPE' => 1,
        ]]);
        $manager = $this->createMock(ServerManager::class);
        $manager->expects(self::never())->method('createSwooleServer');
        (new \ReflectionProperty(ServerManager::class, 'instance'))->setValue(null, $manager);

        // Skip application bootstrap: this tests only the createServer configuration guard.
        $core = (new \ReflectionClass(Core::class))->newInstanceWithoutConstructor();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'MAIN_SERVER.SETTING.task_worker_num is not supported. '
            . 'use MAIN_SERVER.TASK.workerNum to configure EasySwoole task processes instead.'
        );
        $core->createServer();
    }

    public function testCustomTaskConfigurationCanReachServerCreation(): void
    {
        $settings = ['worker_num' => 2];
        Config::getInstance()->load(['MAIN_SERVER' => [
            'PORT' => 9501,
            'SERVER_TYPE' => 2,
            'LISTEN_ADDRESS' => '127.0.0.1',
            'SETTING' => $settings,
            'RUN_MODEL' => 2,
            'SOCK_TYPE' => 1,
            'TASK' => ['workerNum' => 4],
        ]]);
        $boundary = new \RuntimeException('Server creation reached');
        $manager = $this->createMock(ServerManager::class);
        $manager->expects(self::once())->method('createSwooleServer')
            ->with(9501, 2, '127.0.0.1', $settings, 2, 1)
            ->willThrowException($boundary);
        (new \ReflectionProperty(ServerManager::class, 'instance'))->setValue(null, $manager);

        // Stop at the server boundary without binding ports or running application hooks.
        $core = (new \ReflectionClass(Core::class))->newInstanceWithoutConstructor();
        $this->expectExceptionObject($boundary);
        $core->createServer();
    }
}
