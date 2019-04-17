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

class Stop implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'stop';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $force = false;
        if(in_array('force',$args)){
            $force = true;
        }
        if(in_array('produce',$args)){
            Core::getInstance()->setIsDev(false);
        }
        $Conf = Config::getInstance();
        $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            if (!\swoole_process::kill($pid, 0)) {
                return "PID :{$pid} not exist ";
            }
            if ($force) {
                \swoole_process::kill($pid, SIGKILL);
            } else {
                \swoole_process::kill($pid);
            }
            //等待5秒
            $time = time();
            while (true) {
                usleep(1000);
                if (!\swoole_process::kill($pid, 0)) {
                    if (is_file($pidFile)) {
                        unlink($pidFile);
                    }
                    return "server stop at " . date("Y-m-d H:i:s") ;
                    break;
                } else {
                    if (time() - $time > 15) {
                        return "stop server fail.try -f again ";
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
        return $logo.<<<HELP_START
\e[33mOperation:\e[0m
\e[31m  php easyswoole stop [arg1] \e[0m
\e[33mIntro:\e[0m
\e[36m  to stop current easyswoole server \e[0m
\e[33mArg:\e[0m
\e[32m  force \e[0m                   force to kill server
HELP_START;
    }
}
