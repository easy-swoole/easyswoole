<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Application;
use EasySwoole\Command\Console\Console;
use EasySwoole\EasySwoole\Command\DefaultCommand\InstallAction;
use EasySwoole\EasySwoole\Test\Support\{FilesystemTestCase, TestConsole};
use PHPUnit\Framework\Attributes\{PreserveGlobalState, RunTestsInSeparateProcesses};

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class InstallCommandTest extends FilesystemTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        define('EASYSWOOLE_ROOT', $this->directory);
        $this->fixture('composer.json', '{"name":"test/application"}');
    }

    private function application(?int $composerStatus = 0): Application
    {
        $command = new class($composerStatus) extends InstallAction {
            public function __construct(private ?int $composerStatus) {}
            protected function dumpAutoload(): ?int { return $this->composerStatus; }
        };
        $app = new Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\InstallCommand(['' => $command])); return $app;
    }

    public function testNoninteractiveInstallCreatesRequiredResourcesAndPreservesExistingConfig(): void
    {
        $this->fixture('dev.php', '<?php // 已有配置');
        $console = new class extends Console { public function interactive(): bool { return false; } };
        $result = $this->application()->dispatch(['install'], $console);
        self::assertSame(0, $result->exitCode, $result->message);
        self::assertSame('<?php // 已有配置', file_get_contents($this->directory . '/dev.php'));
        foreach (['easyswoole.php', 'produce.php', 'bootstrap.php', 'EasySwooleEvent.php'] as $name) {
            self::assertFileExists($this->directory . '/' . $name);
        }
        self::assertFileDoesNotExist($this->directory . '/App/HttpController/Index.php');
        self::assertStringContainsString('Skipped: App/HttpController/Index.php', $result->message);
        self::assertStringContainsString('Kept: dev.php', $result->message);
        self::assertStringContainsString('install --with-examples', $result->message);
        self::assertStringContainsString('CommandRunner::getInstance()->run', file_get_contents($this->directory . '/easyswoole.php'));
        $composer = json_decode(file_get_contents($this->directory . '/composer.json'), true);
        self::assertSame('App/', $composer['autoload']['psr-4']['App\\']);
    }

    public function testExplicitExamplesFillMissingFilesAndKeepExistingController(): void
    {
        mkdir($this->directory . '/App/HttpController', 0700, true);
        $index = $this->fixture('App/HttpController/Index.php', '<?php // 已有控制器');
        $fixture = (object)['interactive' => false, 'inputs' => [], 'prompts' => []];
        $result = $this->application()->dispatch(['install', '--with-examples'], new TestConsole($fixture));

        self::assertSame(0, $result->exitCode);
        self::assertSame([], $fixture->prompts);
        self::assertSame('<?php // 已有控制器', file_get_contents($index));
        self::assertFileExists($this->directory . '/App/HttpController/Router.php');
        self::assertStringContainsString('Kept: App/HttpController/Index.php', $result->message);
        self::assertStringContainsString('Created: App/HttpController/Router.php', $result->message);
    }

    public function testInteractiveInstallUsesConsoleConfirmation(): void
    {
        $fixture = (object)['interactive' => true, 'inputs' => ['y', 'n'], 'prompts' => []];
        $result = $this->application()->dispatch(['install'], new TestConsole($fixture));
        self::assertSame(0, $result->exitCode);
        self::assertFileExists($this->directory . '/App/HttpController/Index.php');
        self::assertFileDoesNotExist($this->directory . '/App/HttpController/Router.php');
        self::assertCount(2, $fixture->prompts);
    }

    public function testExplicitFalseSkipsExamplesInInteractiveTerminal(): void
    {
        $fixture = (object)['interactive' => true, 'inputs' => [], 'prompts' => []];
        $result = $this->application()->dispatch(['install', '--with-examples=false'], new TestConsole($fixture));

        self::assertSame(0, $result->exitCode);
        self::assertSame([], $fixture->prompts);
        self::assertFileDoesNotExist($this->directory . '/App/HttpController/Index.php');
        self::assertFileDoesNotExist($this->directory . '/App/HttpController/Router.php');
    }

    public function testComposerFailureIsReported(): void
    {
        $console = new class extends Console { public function interactive(): bool { return false; } };
        $result = $this->application(1)->dispatch(['install'], $console);
        self::assertSame(1, $result->exitCode);
        self::assertStringContainsString('composer dump-autoload failed', $result->message);
    }
}
