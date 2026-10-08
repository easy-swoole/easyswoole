<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Swoole\EventHelper;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EventRegisterTest extends TestCase
{
    public static function events(): iterable
    {
        foreach ((new \ReflectionClass(EventRegister::class))->getConstants() as $name => $event) {
            yield $name => [$event];
        }
    }

    #[DataProvider('events')]
    public function testDeclaredEventsAcceptCallbacks(string $event): void
    {
        $register = new EventRegister();
        $callback = static fn () => 'called';
        self::assertSame($register, $register->set($event, $callback));
        self::assertSame([$callback], $register->get($event));
    }

    public function testUnknownEventsAreRejected(): void
    {
        $register = new EventRegister();
        self::assertFalse($register->set('unknown', static fn () => null));
        self::assertFalse($register->add('unknown', static fn () => null));
        self::assertSame([], $register->all());
    }

    public function testHelperReplacesOrAppendsInRegistrationOrder(): void
    {
        $register = new EventRegister();
        $first = static fn () => 'first';
        $second = static fn () => 'second';
        EventHelper::register($register, EventRegister::onRequest, $first);
        EventHelper::registerWithAdd($register, EventRegister::onRequest, $second);
        self::assertSame([$first, $second], $register->get(EventRegister::onRequest));
        EventHelper::register($register, EventRegister::onRequest, $second);
        self::assertSame([$second], $register->get(EventRegister::onRequest));
    }
}
