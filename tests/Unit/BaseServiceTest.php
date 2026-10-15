<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Bridge\BaseService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BaseServiceTest extends TestCase
{
    public static function managementActions(): iterable
    {
        foreach (['serverStatus', 'processInfo', 'crontabInfo', 'stopCrontabRule', 'stopAllCrontabRule',
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
}
