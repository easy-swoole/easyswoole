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
        return new class extends InheritedBridgeCommandForTest {
            public function commandName(): string { return 'test'; }
            public function helper(Package $request, Package $response): void { $this->ping($request, $response); }
            public function __construct() {}
            public function __destruct() {}
            public function __clone() { throw new \LogicException('Clone must not be exposed'); }
            public function __invoke() { throw new \LogicException('Invoke must not be exposed'); }
            public function __serialize(): array { throw new \LogicException('Serialize must not be exposed'); }
            public function __unserialize(array $data): void { throw new \LogicException('Unserialize must not be exposed'); }
            private function privateAction(): void { throw new \LogicException('Private method must not be exposed'); }
            protected function hidden(): void { throw new \LogicException('Protected method must not be exposed'); }
            public static function staticAction(): void { throw new \LogicException('Static method must not be exposed'); }
            public function ping(Package $request, Package $response): void
            {
                $response->setArgs(['echo' => $request->getArgs()['payload']]);
                $response->setStatus(StatusEnum::SUCCESS);
            }
        };
    }

    public static function publicActions(): iterable
    {
        foreach (['ping', 'PING', 'helper', 'inheritedAction'] as $action) {
            yield $action => [$action];
        }
    }

    #[DataProvider('publicActions')]
    public function testValidActionReceivesRequestAndResponse(string $action): void
    {
        $request = new Package();
        $request->setArgs(['action' => $action, 'payload' => 'hello']);
        $response = new Package();
        $this->command()->exec($request, $response);
        self::assertSame(['echo' => 'hello'], $response->getArgs());
        self::assertSame(StatusEnum::SUCCESS, $response->getStatus());
    }

    public static function invalidActions(): iterable
    {
        yield 'unknown' => [['action' => 'missing'], 'missing'];
        foreach (['exec', 'EXEC', 'commandName', 'COMMANDNAME', 'actions', 'hidden', 'privateAction', 'staticAction', 'missingMethod', '__construct', '__CONSTRUCT', '__destruct', '__clone', '__invoke', '__serialize', '__unserialize'] as $action) {
            yield $action => [['action' => $action], $action];
        }
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
    public static function malformedArguments(): iterable
    {
        yield 'array action' => [['action' => []]];
        yield 'integer action' => [['action' => 42]];
        yield 'boolean action' => [['action' => false]];
        yield 'object action' => [['action' => new \stdClass()]];
        yield 'string arguments' => ['invalid'];
        yield 'object arguments' => [new \stdClass()];
    }

    #[DataProvider('malformedArguments')]
    public function testMalformedInputReturnsErrorInsteadOfThrowing(mixed $args): void
    {
        $request = new Package();
        $request->setArgs($args);
        $response = new Package();
        $this->command()->exec($request, $response);
        self::assertSame(StatusEnum::COMMAND_EXEC_ERROR, $response->getStatus());
        self::assertStringContainsString('invalid bridge', $response->getMsg());
    }

    public function testPublicActionIsAvailableWithoutAnAllowlist(): void
    {
        $command = new class extends AbstractCommand {
            public function commandName(): string { return 'closed'; }
            public function helper(Package $request, Package $response): void { $response->setArgs('called'); }
        };
        $request = new Package();
        $request->setArgs(['action' => 'helper']);
        $response = new Package();
        $command->exec($request, $response);
        self::assertSame('called', $response->getArgs());
    }
}

abstract class InheritedBridgeCommandForTest extends AbstractCommand
{
    public function inheritedAction(Package $request, Package $response): void
    {
        $response->setArgs(['echo' => $request->getArgs()['payload']]);
        $response->setStatus(StatusEnum::SUCCESS);
    }
}
