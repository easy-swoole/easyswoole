<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:57
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Validate\Validate;

class Restart implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'restart';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $result = $this->stop();
        if ($result!== true) {
            return $result;
        }
        $this->start();
        return null;
    }

    protected function start()
    {
        // TODO: Implement exec() method.
        Utility::opCacheClear();
        $conf = Config::getInstance();
        $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
        //create main Server
        Core::getInstance()->globalInitialize()->createServer();
        echo "server restart at " . date("Y-m-d H:i:s").PHP_EOL;
        Core::getInstance()->start();
        return null;
    }

    protected function stop()
    {
        $Conf = Config::getInstance();
        $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            if (!\swoole_process::kill($pid, 0)) {
                return "PID :{$pid} not exist ";
            }
            //强制停止
            \swoole_process::kill($pid, SIGKILL);
            //等待5秒
            $time = time();
            while (true) {
                usleep(1000);
                if (!\swoole_process::kill($pid, 0)) {
                    if (is_file($pidFile)) {
                        unlink($pidFile);
                    }
                    return true;
                    break;
                } else {
                    if (time() - $time > 15) {
                        return "stop server fail , try : php easyswoole stop force";
                        break;
                    }
                }
            }
            return 'stop server fail';
        } else {
            return "PID file does not exist, please check whether to run in the daemon mode!";
        }
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo . <<<HELP_START
\e[33mOperation:\e[0m
\e[31m  php easyswoole restart [arg1] \e[0m
\e[33mIntro:\e[0m
\e[36m  to restart current easyswoole server \e[0m
\e[33mArg:\e[0m
\e[32m  produce \e[0m                     load produce.php
HELP_START;
    }
}
