<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use PHPUnit\Framework\TestCase;

final class ServerManagerTest extends TestCase
{
    public function testInitialStateAndUnknownSubServer(): void
    {
        $manager = new ServerManager();
        self::assertFalse($manager->isStart());
        self::assertFalse($manager->isDaemonize());
        self::assertInstanceOf(EventRegister::class, $manager->getEventRegister());
        self::assertSame($manager->getEventRegister(), $manager->getEventRegister());
        self::assertNull($manager->getEventRegister('unknown'));
        self::assertNull($manager->getSwooleServer('unknown'));
    }
}
