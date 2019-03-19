<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-18
 * Time: 23:21
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Config;

class Phar implements CommandInterface
{

    public function commandName(): string
    {
        return 'phar';
    }

    public function exec(array $args): ?string
    {
        $name = array_shift($args);
        if (empty($name)) {
            $name = 'easyswoole.phar';
        } else {
            $name = "{$name}.phar";
        }
        $phar = new \Phar($name);
        $pharConfig = Config::getInstance()->getConf('PHAR');
        $excludes = $pharConfig['EXCLUDE'] ?? [];
        $rdi = new \RecursiveDirectoryIterator(EASYSWOOLE_ROOT, \FilesystemIterator::SKIP_DOTS);
        $rcfi = new \RecursiveCallbackFilterIterator($rdi, function (\SplFileInfo $current, $key, $iterator) use ($excludes) {
            $ei = new \ArrayIterator($excludes);
            foreach ($ei as $exclude) {
                if (is_file($exclude)) {
                    $fileFullPath = EASYSWOOLE_ROOT . '/' . ltrim($exclude, '/');
                    if ($current->getPathname() == $fileFullPath) {
                        return false;
                    }
                }
                if (is_dir($exclude)) {
                    $dirFullPath = EASYSWOOLE_ROOT . '/' . ltrim($exclude, '/');
                    if (substr($current->getPathname(), 0, strlen($dirFullPath)) == $dirFullPath) {
                        return false;
                    }
                }
            }
            return true;
        });
        $phar->buildFromIterator(new \RecursiveIteratorIterator($rcfi), EASYSWOOLE_ROOT);
        $phar->setStub($phar->createDefaultStub('vendor/easyswoole/easyswoole/bin/easyswoole'));
        return "build {$name} finish";
    }

    public function help(array $args): ?string
    {
        return <<<HELP
\e[33mOperation:\e[0m
\e[31m  php easyswoole phar [name] \e[0m
\e[33mIntro:\e[0m
\e[36m  Package the current project as PHAR \e[0m
HELP;
    }
}