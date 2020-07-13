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
use Swoole\Coroutine\Scheduler;

class Server implements CommandInterface
{
    public function commandName(): string
    {
        return 'server';
    }

    public function desc(): string
    {
        return 'easyswoole server';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('start', '启动');
        $commandHelp->addAction('stop', '停止');
        $commandHelp->addAction('reload', '重启worker');
        $commandHelp->addAction('restart', '重启EasySwoole');
        $commandHelp->addAction('status', '查看EasySwoole状态');
        $commandHelp->addActionOpt('-d', '守护进程方式启动');
        $commandHelp->addActionOpt('--force', '强行停止');
        return $commandHelp;
    }

    public function exec(): string
    {
        $action = CommandManager::getInstance()->getArg(0);
        if (method_exists($this, $action)) {
            Core::getInstance()->initialize();
            return $this->$action();
        } else {
            if (!empty($action)) {
                return Color::warning("The command '{$action}' is not exists!");
            } else {
                return '';
            }
        }

    }

    protected function start()
    {
        $conf = Config::getInstance();
        $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);

        // php easyswoole start -d
        $daemonize = CommandManager::getInstance()->issetOpt('d');
        $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);

        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        $displayItem = [];
        switch ($serverType) {
            case EASYSWOOLE_SERVER:
            {
                $serverType = 'SWOOLE_SERVER';
                break;
            }
            case EASYSWOOLE_WEB_SERVER:
            {
                $serverType = 'SWOOLE_WEB';
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:
            {
                $serverType = 'SWOOLE_WEB_SOCKET';
                break;
            }
            default:
            {
                $serverType = 'UNKNOWN';
            }
        }
        $displayItem['main server'] = $serverType;
        $displayItem['listen address'] = $conf->getConf('MAIN_SERVER.LISTEN_ADDRESS');
        $displayItem['listen port'] = $conf->getConf('MAIN_SERVER.PORT');
        $data = $conf->getConf('MAIN_SERVER.SETTING');
        if (empty($data['user'])) {
            $data['user'] = get_current_user();
        }
        $displayItem = $displayItem + $data;
        $displayItem['swoole version'] = phpversion('swoole');
        $displayItem['php version'] = phpversion();
        $displayItem['easyswoole version'] = SysConst::EASYSWOOLE_VERSION;
        $displayItem['develop/produce'] = Core::getInstance()->runMode() ? 'dev' : 'produce';
        $displayItem['temp dir'] = EASYSWOOLE_TEMP_DIR;
        $displayItem['log dir'] = EASYSWOOLE_LOG_DIR;

        $msg = '';
        foreach ($displayItem as $key => $value) {
            $msg .= Utility::displayItem($key, $value) . "\n";
        }
        echo $msg;
        Core::getInstance()->initialize()->globalInitialize()->createServer()->start();
        return 'success';
    }

    protected function stop()
    {
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        $msg = '';
        if (file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            if (!\Swoole\Process::kill($pid, 0)) {
                $msg = "pid :{$pid} not exist ";
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
                        break;
                    } else {
                        if (time() - $time > 15) {
                            $msg = "stop server fail for pid:{$pid} , try [php easyswoole stop force] again";
                            break;
                        }
                    }
                }
            }
        } else {
            $msg = "pid file does not exist, please check whether to run in the daemon mode!";
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
                $msg = "pid :{$pid} not exist ";
            } else {
                \Swoole\Process::kill($pid, SIGUSR1);
                $msg = "send server reload command to pid:{$pid} at " . date("Y-m-d H:i:s");
            }
        } else {
            $msg = "pid file does not exist, please check whether to run in the daemon mode!";
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
        $args = CommandManager::getInstance()->getArgs();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $result = Utility::bridgeCall($this->commandName(), function (Package $package) {
                $data = $package->getArgs();
                $data['start_time'] = date('Y-m-d H:i:s', $data['start_time']);
                $msg = '';
                foreach ($data as $key => $val) {
                    $msg .= Utility::displayItem($key, $val) . "\n";
                }
                return $msg;
            }, 'info');
        });
        $run->start();
        return $result;
    }
}