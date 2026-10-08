<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Command\Utility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CommandUtilityTest extends TestCase
{
    public static function displayValues(): iterable
    {
        yield [true, 'true'];
        yield [false, 'false'];
        yield [null, 'null'];
        yield [0, '0'];
        yield ['hello', 'hello'];
        yield [['名称' => 'http://localhost'], '{"名称":"http://localhost"}'];
    }

    #[DataProvider('displayValues')]
    public function testDisplayValueFormatting(mixed $value, string $expected): void
    {
        self::assertSame("\e[32m" . str_pad('name', 30) . "\e[34m" . $expected . "\e[0m", Utility::displayItem('name', $value));
    }
}
