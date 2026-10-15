<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\CommandLine;
use EasySwoole\Command\Bean\Result;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ServerStartTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLogoIsFollowedByTheSameEnvironmentLayoutAsStatus(): void
    {
        define('EASYSWOOLE_SERVER', 1);
        define('EASYSWOOLE_WEB_SERVER', 2);
        define('EASYSWOOLE_WEB_SOCKET_SERVER', 3);
        define('EASYSWOOLE_TEMP_DIR', '/tmp/app');
        define('EASYSWOOLE_LOG_DIR', '/tmp/app-log');
        Config::getInstance()->load(['MAIN_SERVER' => [
            'SERVER_TYPE' => 2, 'LISTEN_ADDRESS' => '127.0.0.1', 'PORT' => 9501,
            'SETTING' => ['worker_num' => 2, 'reload_async' => true,
                'pid_file' => '/tmp/app/dev.pid', 'log_file' => '/tmp/app-log/dev.swoole.log'],
        ]]);
        $core = $this->createMock(Core::class);
        $core->method('runMode')->willReturn('dev');
        $core->expects(self::once())->method('createServer')->willReturn($core);
        $core->expects(self::once())->method('start');
        (new \ReflectionProperty(Core::class, 'instance'))->setValue(null, $core);
        $command = new class extends Server {
            public function statusOutput(array $environment): string
            {
                return $this->formatServerStatus(['env' => $environment, 'runtime' => []]);
            }
        };
        ob_start();
        try {
            ($command->getActions()['start']->getCallback())(new Caller('server', 'start', new CommandLine()), new Result());
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        $environment = Utility::generateEnv(Config::getInstance(), 'dev');
        $status = $command->statusOutput($environment);
        $expected = explode("\n\nRuntime\n", explode("\n\nEnvironment\n", $status)[1])[0];
        self::assertStringContainsString(Utility::easySwooleLog(), $output);
        self::assertStringContainsString("\nEnvironment\n" . $expected . "\n", $output);
        self::assertMatchesRegularExpression('/main server +: SWOOLE_WEB +listen address +: 127.0.0.1/', $output);
        foreach (['pid_file', 'log_file', 'temp dir', 'log dir'] as $key) {
            self::assertMatchesRegularExpression('/^  ' . preg_quote($key, '/') . ' +: [^\n]+$/m', $output);
        }
    }
}
