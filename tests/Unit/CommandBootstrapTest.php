<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Command\CommandRunner;
use EasySwoole\EasySwoole\Test\Support\FilesystemTestCase;
use PHPUnit\Framework\Attributes\{DataProvider, PreserveGlobalState, RunTestsInSeparateProcesses};

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class CommandBootstrapTest extends FilesystemTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        define('EASYSWOOLE_ROOT', $this->directory);
    }

    public static function entrypoints(): iterable
    {
        yield 'Composer 入口' => ['bin/easyswoole.php'];
        yield '项目入口模板' => ['src/Resource/easyswoole'];
    }

    #[DataProvider('entrypoints')]
    public function testMissingAutoloaderUsesStderrAndExitsWithFailure(string $source): void
    {
        $entrypoint = $this->directory . '/easyswoole.php';
        self::assertTrue(copy(dirname(__DIR__, 2) . '/' . $source, $entrypoint));
        $process = proc_open(
            [PHP_BINARY, $entrypoint, '--help'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->directory,
        );
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        self::assertSame(1, proc_close($process));
        self::assertSame('', $stdout);
        self::assertStringContainsString('autoload.php', $stderr);
    }

    public function testMissingEventReturnsExecutionFailure(): void
    {
        $result = (new CommandRunner())->dispatch(['server', 'status']);
        self::assertSame(1, $result->exitCode);
        self::assertStringContainsString('EasySwooleEvent.php', $result->message);
    }

    public function testMissingConfigReturnsExecutionFailure(): void
    {
        $this->fixture('EasySwooleEvent.php', '<?php');
        $result = (new CommandRunner())->dispatch(['server', 'status', '--mode=missing']);
        self::assertSame(1, $result->exitCode);
        self::assertStringContainsString('missing.php', $result->message);
    }
}
