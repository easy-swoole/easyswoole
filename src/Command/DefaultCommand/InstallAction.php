<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\FrameworkAction;

class InstallAction extends FrameworkAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('install')
            ->description('EasySwoole framework installation')
            ->option(Option::flag('with-examples', 'generate missing HTTP example files without prompting'));
    }

    protected function handle(Input $input): CommandResult
    {
        $manifestPath = EASYSWOOLE_ROOT . '/composer.json';
        $manifest = file_get_contents($manifestPath);
        if ($manifest === false) {
            return CommandResult::failure('cannot read composer.json');
        }
        $composer = json_decode($manifest, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($composer)) {
            return CommandResult::failure('composer.json must contain a JSON object');
        }
        $updatedManifest = null;
        if (!isset($composer['autoload']['psr-4']['App\\'])) {
            $composer['autoload']['psr-4']['App\\'] = 'App/';
            $updatedManifest = json_encode($composer, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
        }

        $examples = [
            'App/HttpController/Index.php' => 'Http/Index._php',
            'App/HttpController/Router.php' => 'Http/Router._php',
        ];
        $resources = [
            'easyswoole.php' => 'easyswoole',
            'dev.php' => 'Config._php',
            'produce.php' => 'Config._php',
            'bootstrap.php' => 'bootstrap._php',
            'EasySwooleEvent.php' => 'EasySwooleEvent._php',
        ] + $examples;

        $interactive = $this->console->interactive();
        $withExamples = $input->provided('with-examples') ? $input->option('with-examples') : null;
        $summary = [];
        $skippedExamples = false;
        foreach ($resources as $file => $template) {
            $destination = EASYSWOOLE_ROOT . '/' . $file;
            $exists = file_exists($destination);
            $example = isset($examples[$file]);

            // 示例由用户选择，项目已有文件默认保留。CLI 入口每次按当前框架版本更新。
            if ($example && !($withExamples ?? ($exists || ($interactive && $this->console->confirm("Generate {$file}?"))))) {
                $summary[] = ($exists ? 'Kept: ' : 'Skipped: ') . $file;
                $skippedExamples = $skippedExamples || !$exists;
                continue;
            }
            if ($exists && $file !== 'easyswoole.php'
                && (!$interactive || !$this->console->confirm("{$file} already exists; replace it?"))) {
                $summary[] = "Kept: {$file}";
                continue;
            }

            $directory = dirname($destination);
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException("cannot create directory {$directory}");
            }
            if (!copy(__DIR__ . '/../../Resource/' . $template, $destination)) {
                throw new \RuntimeException("cannot write {$file}");
            }
            $summary[] = ($exists ? 'Updated: ' : 'Created: ') . $file;
        }

        if ($updatedManifest !== null) {
            if (file_put_contents($manifestPath, $updatedManifest) === false) {
                return CommandResult::failure(implode(PHP_EOL, [...$summary, 'cannot update composer.json']));
            }
            $summary[] = 'Updated: composer.json';
        }
        if ($skippedExamples && $withExamples === null) {
            $summary[] = 'To generate missing examples, run php vendor/bin/easyswoole.php install --with-examples';
        }

        $exitCode = $this->dumpAutoload();
        if ($exitCode === null) {
            $summary[] = 'Run composer dump-autoload manually to finish installation.';
        } elseif ($exitCode !== 0) {
            return CommandResult::failure(implode(PHP_EOL, [
                'Installation incomplete: composer dump-autoload failed',
                ...$summary,
                'Run composer dump-autoload again after fixing the Composer error.',
            ]));
        } else {
            $summary[] = 'install EasySwoole Success';
        }

        return CommandResult::success(implode(PHP_EOL, $summary));
    }

    protected function dumpAutoload(): ?int
    {
        if (!function_exists('exec')) {
            return null;
        }
        exec('composer --working-dir=' . escapeshellarg(EASYSWOOLE_ROOT) . ' dump-autoload', $output, $exitCode);
        return $exitCode;
    }
}
