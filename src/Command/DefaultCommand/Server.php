<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Server implements CommandInterface
{
    public function commandName(): string
    {
        return 'server';
    }

    public function desc(): string
    {
        return 'Easyswoole server';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('start', 'start-up');
        $commandHelp->addAction('stop', 'stop it');
        $commandHelp->addAction('reload', 'reload worker process');
        $commandHelp->addAction('restart', 'restart EasySwoole server');
        $commandHelp->addAction('status', 'view EasySwoole status');
        $commandHelp->addActionOpt('-d', 'start as a daemon');
        $commandHelp->addActionOpt('-force', 'stop by force');
        $commandHelp->addActionOpt('-mode=dev', 'run mode,dev is default mode');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0);
        if (method_exists($this, $action) && $action != 'help') {
            Core::getInstance()->initialize();
            return $this->$action();
        }

        return CommandManager::getInstance()->displayCommandHelp($this->commandName());
    }

    protected function start()
    {
        defined('EASYSWOOLE_RUNNING') or define('EASYSWOOLE_RUNNING', true);
        $conf = Config::getInstance();
        // php easyswoole.php server start -d
        $daemonize = CommandManager::getInstance()->issetOpt('d');
        if ($daemonize) {
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);
        }

        if (empty($conf->getConf('MAIN_SERVER.SETTING.user'))) {
            $conf->setConf('MAIN_SERVER.SETTING.user',get_current_user());
        }

        $displayItem = Utility::createServerDisplayItem(Config::getInstance());
        $msg = Color::green(Utility::easySwooleLog()) . "\n";
        foreach ($displayItem as $key => $value) {
            $msg .= Utility::displayItem($key, $value) . "\n";
        }
        echo $msg;
        Core::getInstance()->createServer()->start();
    }

    protected function stop()
    {
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        $msg = '';
        if (file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            if (!\Swoole\Process::kill($pid, 0)) {
                $msg = Color::danger("pid :{$pid} not exist ");
                unlink($pidFile);
            } else {
                $force = CommandManager::getInstance()->issetOpt('force');
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
        return $msg;
    }

    protected function reload()
    {
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            Utility::opCacheClear();
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
        return $msg;
    }

    protected function restart()
    {
        $msg = $this->stop();
        $this->start();
        return $msg;
    }

    protected function status()
    {
        $run = new Scheduler();
        $run->add(function () use (&$result) {
            $result = Utility::bridgeCall('status', function (Package $package) {
                $displayItem = $package->getArgs();
                $msg = Color::green(Utility::easySwooleLog()) . "\n";
                foreach ($displayItem as $key => $value) {
                    $msg .= Utility::displayItem($key, $value) . "\n";
                }
                return $msg;
            }, 'call');
        });
        $run->start();
        return $result;
    }
}