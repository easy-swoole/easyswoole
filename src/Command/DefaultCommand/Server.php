<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Scheduler;
use EasySwoole\EasySwoole\Command\Console\Watch;

class Server extends AbstractCommand
{

    function name(): string
    {
        return 'server';
    }

    function description(): string
    {
        return 'EasySwoole server manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init():void
    {
        $action = new Action('start','start EasySwoole server');
        $action->addOption(new Option('d','start EasySwoole with daemonize mode'));
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller) {
            defined('EASYSWOOLE_RUNNING') or define('EASYSWOOLE_RUNNING', true);
            $conf = Config::getInstance();
            $daemonize = $caller->commandLine->hasOption('d');
            if ($daemonize) {
                $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);
            }
            if (empty($conf->getConf('MAIN_SERVER.SETTING.user'))) {
                $conf->setConf('MAIN_SERVER.SETTING.user',get_current_user());
            }

            $displayItem = Utility::generateEnv(Config::getInstance(),Core::getInstance()->runMode());
            $msg = Color::green(Utility::easySwooleLog()) . "\nEnvironment\n";
            $msg .= $this->formatStatusSection($displayItem, true) . "\n";
            echo $msg;
            Core::getInstance()->createServer()->start();
        });

        $this->registerAction($action);

        $action = new Action('stop','stop EasySwoole server');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new Option('force','stop EasySwoole server force with SIGKILL'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
            $msg = '';
            if (file_exists($pidFile)) {
                $pid = intval(file_get_contents($pidFile));
                if (!\Swoole\Process::kill($pid, 0)) {
                    $msg = Color::danger("pid :{$pid} not exist ");
                    unlink($pidFile);
                } else {
                    $force = $caller->commandLine->hasOption('force');
                    if ($force) {
                        \Swoole\Process::kill($pid, SIGKILL);
                    } else {
                        \Swoole\Process::kill($pid);
                    }
                    //等待5秒
                    $time = time();
                    while (true) {
                        usleep(1000);
                        if (!\Swoole\Process::kill($pid, 0)) {
                            if (is_file($pidFile)) {
                                unlink($pidFile);
                            }
                            $msg = "server stop for pid {$pid} at " . date("Y-m-d H:i:s");
                            $msg = Color::success($msg);
                            break;
                        } else {
                            if (time() - $time > 15) {
                                $msg = Color::danger("stop server fail for pid:{$pid} , try [php easyswoole.php server stop -force] again");
                                break;
                            }
                        }
                    }
                }
            } else {
                $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
            }
            $result->msg = $msg;
        });

        $this->registerAction($action);

        $action = new Action('reload','reload EasySwoole server');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                if (!\Swoole\Process::kill($pid, 0)) {
                    $msg = Color::danger("pid :{$pid} not exist ");
                } else {
                    \Swoole\Process::kill($pid, SIGUSR1);
                    $msg = "send server reload command to pid:{$pid} at " . date("Y-m-d H:i:s");
                    $msg = Color::success($msg);
                }
            } else {
                $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
            }
            $result->msg = $msg;
        });
        $this->registerAction($action);

        $action = new Action('status','get EasySwoole server status');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));

        $action->setCallback(function (Caller $caller, Result $result): void {
            try {
                $package = $this->requestServerStatus();
                if ($package->getStatus() !== StatusEnum::SUCCESS) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = $package->getMsg();
                    return;
                }
                $data = $package->getArgs();
                if (!is_array($data) || !isset($data['env'], $data['runtime'])
                    || !is_array($data['env']) || !is_array($data['runtime'])) {
                    throw new \UnexpectedValueException('invalid server status returned by Bridge');
                }
                $result->result = $data;
                $result->msg = $this->formatServerStatus($data);
            } catch (\Throwable $throwable) {
                $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                $result->msg = $throwable->getMessage();
            }
        });

        Watch::attach($action);
        $this->registerAction($action);
    }

    protected function requestServerStatus(): Package
    {
        $package = null;
        $error = null;
        $scheduler = new Scheduler();
        $scheduler->add(static function () use (&$package, &$error): void {
            try {
                $package = Bridge::bridgeCall('serverStatus');
            } catch (\Throwable $throwable) {
                $error = $throwable;
            }
        });
        $scheduler->start();
        if ($error !== null) {
            throw $error;
        }
        return $package;
    }

    protected function formatServerStatus(array $data): string
    {
        $lines = ['Server status'];
        foreach (['Environment' => 'env', 'Runtime' => 'runtime'] as $title => $section) {
            $lines[] = '';
            $lines[] = $title;
            $lines[] = $this->formatStatusSection($data[$section], $section === 'env');
        }
        return implode(PHP_EOL, $lines);
    }

    protected function formatStatusSection(array $values, bool $environment): string
    {
        $lines = [];
        if (!$values) {
            $lines[] = '  No data available.';
        }
        $items = [];
        $paths = [];
        $keyWidth = $values
            ? max(array_map(static fn ($key) => strlen((string)$key), array_keys($values))) : 28;
        foreach ($values as $key => $value) {
            if (!$environment && $key === 'start_time') {
                $value = is_int($value) && $value > 0 ? date('Y-m-d H:i:s', $value) : '-';
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '-';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            }
            $key = preg_replace('/[\x00-\x1f\x7f]/', '?', (string)$key);
            $value = preg_replace('/[\x00-\x1f\x7f]/', '?', (string)$value);
            $item = str_pad($key, $keyWidth) . ' : ' . $value;
            if ($environment && in_array($key, ['pid_file', 'log_file', 'temp dir', 'log dir'], true)) {
                $paths[] = $item;
            } else {
                $items[] = $item;
            }
        }
        if ($items) {
            $rows = array_chunk($items, 2);
            $leftWidth = max(array_map(static fn (array $row) => strlen($row[0]), $rows)) + 4;
            foreach ($rows as $row) {
                $lines[] = '  ' . (isset($row[1]) ? str_pad($row[0], $leftWidth) . $row[1] : $row[0]);
            }
        }
        foreach ($paths as $path) {
            $lines[] = '  ' . $path;
        }
        return implode(PHP_EOL, $lines);
    }


}
