<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Bridge\BaseService;
use EasySwoole\EasySwoole\Task\TaskManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BaseServiceTest extends TestCase
{
    public static function managementActions(): iterable
    {
        foreach (['serverStatus', 'processInfo', 'taskStatus', 'taskWorkerPidList', 'crontabInfo', 'stopCrontabRule', 'stopAllCrontabRule',
            'resumeCrontabRule', 'resumeAllCrontabRule', 'runCrontabJobNow', 'setCrontabRule'] as $action) {
            yield $action => [$action];
        }
    }

    #[DataProvider('managementActions')]
    public function testManagementActionsRemainDispatchable(string $action): void
    {
        // Stub the process/socket boundary while exercising reflection-based dispatch.
        $command = new class extends BaseService {
            public array $calls = [];
            public function serverStatus(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function processInfo(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function taskStatus(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function taskWorkerPidList(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function crontabInfo(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function stopCrontabRule(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function stopAllCrontabRule(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function resumeCrontabRule(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function resumeAllCrontabRule(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function runCrontabJobNow(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
            public function setCrontabRule(Package $request, Package $responsePackage): void { $this->calls[] = [__FUNCTION__, $request, $responsePackage]; }
        };
        $request = new Package();
        $request->setArgs(['action' => $action, 'taskName' => 'example']);
        $response = new Package();
        $response->setStatus(StatusEnum::SUCCESS);
        $command->exec($request, $response);
        self::assertSame([[$action, $request, $response]], $command->calls);
        self::assertSame(StatusEnum::SUCCESS, $response->getStatus());
    }

    public static function taskWorkerStatuses(): iterable
    {
        yield 'no workers' => [[], []];
        yield 'one worker' => [[[ 'pid' => 1234, 'accepting' => 1, 'running' => 0 ]], [1234]];
        yield 'multiple nonconsecutive indexes' => [
            [0 => ['pid' => 1234, 'accepting' => 1], 3 => ['pid' => 5678, 'accepting' => 1]],
            [1234, 5678],
        ];
        yield 'draining worker remains visible' => [
            [['pid' => 1234, 'accepting' => 0, 'running' => 1]], [1234],
        ];
    }

    #[DataProvider('taskWorkerStatuses')]
    public function testTaskWorkerPidListUsesRunningTaskManager(array $status, array $expected): void
    {
        $property = new \ReflectionProperty(TaskManager::class, 'instance');
        $previous = $property->getValue();
        $manager = $this->createMock(TaskManager::class);
        $manager->expects(self::once())->method('status')->willReturn($status);
        $property->setValue(null, $manager);
        try {
            $request = new Package();
            $request->setArgs(['action' => 'taskWorkerPidList']);
            $response = new Package();
            $response->setStatus(StatusEnum::SUCCESS);
            (new BaseService())->exec($request, $response);
            self::assertSame(StatusEnum::SUCCESS, $response->getStatus());
            self::assertSame($expected, $response->getArgs());
        } finally {
            $property->setValue(null, $previous);
        }
    }

    public function testTaskStatusReturnsSharedWorkerRows(): void
    {
        $property = new \ReflectionProperty(TaskManager::class, 'instance');
        $previous = $property->getValue();
        $rows = [0 => ['pid' => 123, 'accepting' => 0, 'running' => 1, 'success' => 4, 'fail' => 2]];
        $manager = $this->createMock(TaskManager::class);
        $manager->expects(self::once())->method('status')->willReturn($rows);
        $property->setValue(null, $manager);
        try {
            $request = new Package(); $request->setArgs(['action' => 'taskStatus']);
            $response = new Package(); $response->setStatus(StatusEnum::SUCCESS);
            (new BaseService())->exec($request, $response);
            self::assertSame($rows, $response->getArgs());
        } finally { $property->setValue(null, $previous); }
    }

}
