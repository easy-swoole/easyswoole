<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-25
 * Time: 11:12
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;

class Reload implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'reload';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $all = false;
        if(in_array('all',$args)){
            $all = true;
        }
        if(in_array('produce',$args)){
            Core::getInstance()->setIsDev(false);
        }
        $Conf = Config::getInstance();
        $res = '';
        $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            if (!$all) {
                $sig = SIGUSR2;
                $res = $res.Utility::displayItem('reloadType',"only-task")."\n";
            } else {
                $sig = SIGUSR1;
                $res = $res.Utility::displayItem('reloadType',"all-worker")."\n";
            }
            Utility::opCacheClear();
            $pid = file_get_contents($pidFile);
            if (!\swoole_process::kill($pid, 0)) {
                return "pid :{$pid} not exist ";
            }
            \swoole_process::kill($pid, $sig);
            return $res. "send server reload command at " . date("Y-m-d H:i:s");
        } else {
            return "PID file does not exist, please check whether to run in the daemon mode!";
        }
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return  $logo.<<<HELP_RELOAD
\e[33mOperation:\e[0m
\e[31m  php easyswoole reload [arg1]\e[0m
\e[33mIntro:\e[0m
\e[36m  you can reload current easyswoole server\e[0m
\e[33mAgs:\e[0m
\e[32m  all \e[0m           reload all worker and task_worker process
HELP_RELOAD;
    }
}
