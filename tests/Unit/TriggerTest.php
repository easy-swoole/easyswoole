<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerLocation;
use EasySwoole\EasySwoole\Trigger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TriggerTest extends TestCase
{
    public function testExplicitErrorLocationIsForwarded(): void
    {
        $location = new TriggerLocation();
        $location->setFile('app.php');
        $location->setLine(42);
        $handler = $this->createMock(TriggerInterface::class);
        $handler->expects(self::once())->method('error')->with('failure', E_USER_WARNING, $location);
        (new Trigger($handler))->error('failure', E_USER_WARNING, $location);
        self::assertSame('app.php', $location->getFile());
        self::assertSame(42, $location->getLine());
    }

    public function testAutomaticLocationPointsAtCaller(): void
    {
        $handler = $this->createMock(TriggerInterface::class);
        $line = 0;
        $handler->expects(self::once())->method('error')->with('failure', E_USER_ERROR,
            self::callback(static function (TriggerLocation $location) use (&$line): bool {
                return $location->getFile() === __FILE__ && $location->getLine() === $line;
            }));
        $trigger = new Trigger($handler);
        $line = __LINE__ + 1;
        $trigger->error('failure');
    }

    public static function callbackResults(): iterable
    {
        yield 'false intercepts' => [false, false];
        yield 'null continues' => [null, true];
        yield 'zero continues' => [0, true];
        yield 'true continues' => [true, true];
    }

    #[DataProvider('callbackResults')]
    public function testErrorCallbackOnlyInterceptsStrictFalse(mixed $result, bool $forward): void
    {
        $handler = $this->createMock(TriggerInterface::class);
        $handler->expects($forward ? self::once() : self::never())->method('error');
        $trigger = new Trigger($handler);
        $location = new TriggerLocation();
        $seen = [];
        $trigger->setOnError(static function (...$args) use (&$seen, $result) { $seen = $args; return $result; });
        $trigger->error('failure', E_NOTICE, $location);
        self::assertSame(['failure', E_NOTICE, $location], $seen);
    }

    #[DataProvider('callbackResults')]
    public function testThrowableCallbackOnlyInterceptsStrictFalse(mixed $result, bool $forward): void
    {
        $exception = new \RuntimeException('failure');
        $handler = $this->createMock(TriggerInterface::class);
        $handler->expects($forward ? self::once() : self::never())->method('throwable')->with($exception);
        $trigger = new Trigger($handler);
        $seen = null;
        $trigger->setOnThrowable(static function ($value) use (&$seen, $result) { $seen = $value; return $result; });
        $trigger->throwable($exception);
        self::assertSame($exception, $seen);
    }

    public function testThrowableIsForwardedWithoutCallback(): void
    {
        $exception = new \RuntimeException('failure');
        $handler = $this->createMock(TriggerInterface::class);
        $handler->expects(self::once())->method('throwable')->with($exception);
        (new Trigger($handler))->throwable($exception);
    }
}
