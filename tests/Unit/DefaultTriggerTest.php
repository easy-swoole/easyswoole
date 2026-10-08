<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\AbstractInterface\Log\LoggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerLocation;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Utility\DefaultTrigger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DefaultTriggerTest extends TestCase
{
    private mixed $previousLogger;

    protected function setUp(): void
    {
        $this->previousLogger = (new \ReflectionProperty(Logger::class, 'instance'))->getValue();
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(Logger::class, 'instance'))->setValue(null, $this->previousLogger);
    }

    private function installLogger(LoggerInterface $handler): void
    {
        $logger = new Logger($handler);
        $logger->displayConsole(false);
        (new \ReflectionProperty(Logger::class, 'instance'))->setValue(null, $logger);
    }

    public static function errorLevels(): iterable
    {
        foreach ([E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR] as $code) {
            yield [$code, LogLevelEnum::ERROR];
        }
        foreach ([E_WARNING, E_USER_WARNING, E_COMPILE_WARNING, E_RECOVERABLE_ERROR] as $code) {
            yield [$code, LogLevelEnum::WARNING];
        }
        foreach ([E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED] as $code) {
            yield [$code, LogLevelEnum::NOTICE];
        }
        yield [0, LogLevelEnum::INFO];
    }

    #[DataProvider('errorLevels')]
    public function testErrorLevelMappingAndLocation(int $code, LogLevelEnum $level): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::once())->method('log')->with('failure at file:app.php line:42', $level, 'trigger')->willReturn(true);
        $this->installLogger($handler);
        $location = new TriggerLocation();
        $location->setFile('app.php');
        $location->setLine(42);
        (new DefaultTrigger())->error('failure', $code, $location);
    }

    public function testThrowableIncludesLocationAndCustomCategory(): void
    {
        $exception = new \RuntimeException('failure');
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::once())->method('log')->with(
            "failure at file:{$exception->getFile()} line:{$exception->getLine()}", LogLevelEnum::ERROR, 'custom'
        )->willReturn(true);
        $this->installLogger($handler);
        (new DefaultTrigger())->throwable($exception, 'custom');
    }

    public function testThrowableDefaultsToTriggerCategory(): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::once())->method('log')->with(self::stringContains('failure at file:'), LogLevelEnum::ERROR, 'trigger')->willReturn(true);
        $this->installLogger($handler);
        (new DefaultTrigger())->throwable(new \RuntimeException('failure'));
    }
}
