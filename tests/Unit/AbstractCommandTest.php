<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AbstractCommandTest extends TestCase
{
    private function command(): AbstractCommand
    {
        return new class extends AbstractCommand {
            public function commandName(): string { return 'test'; }
            public function ping(Package $request, Package $response): void
            {
                $response->setArgs(['echo' => $request->getArgs()['payload']]);
                $response->setStatus(StatusEnum::SUCCESS);
            }
        };
    }

    public function testValidActionReceivesRequestAndResponse(): void
    {
        $request = new Package();
        $request->setArgs(['action' => 'ping', 'payload' => 'hello']);
        $response = new Package();
        $this->command()->exec($request, $response);
        self::assertSame(['echo' => 'hello'], $response->getArgs());
        self::assertSame(StatusEnum::SUCCESS, $response->getStatus());
    }

    public static function invalidActions(): iterable
    {
        yield 'unknown' => [['action' => 'missing'], 'missing'];
        yield 'omitted' => [[], ''];
        yield 'null arguments' => [null, ''];
    }

    #[DataProvider('invalidActions')]
    public function testInvalidActionReturnsCommandError(?array $args, string $action): void
    {
        $request = new Package();
        $request->setArgs($args);
        $response = new Package();
        $this->command()->exec($request, $response);
        self::assertSame(StatusEnum::COMMAND_EXEC_ERROR, $response->getStatus());
        self::assertSame("baseService bridge command [{$action}] not exists", $response->getMsg());
    }
}
