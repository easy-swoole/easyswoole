<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Process implements CommandInterface
{

    public function commandName(): string
    {
        return 'process';
    }

    public function exec(array $args): ?string
    {
        /*
         * php easyswoole process kill PID
         * php easyswoole process kill PID -f
         * php easyswoole process kill GroupName -f
         * php easyswoole process killAll
         * php easyswoole process killAll -f
         */
        $file = EASYSWOOLE_TEMP_DIR.'/process.json';
        if(file_exists($file)){
            $action = array_shift($args);
            $json = json_decode(file_get_contents($file),true);
            $list = [];
            switch ($action){
                case 'kill':{
                    $pidOrGroupName = array_shift($args);
                    $option = array_shift($args);
                    if(is_numeric($pidOrGroupName)){
                        $list[] = $pidOrGroupName;
                    }else{
                        foreach ($json as $pid => $value){
                            if($value['group'] == $pidOrGroupName){
                                $list[] = $pid;
                            }
                        }
                    }
                    break;
                }
                case 'killAll':{
                    $option = array_shift($args);
                    foreach ($json as $pid => $value){
                        $list[] = $pid;
                    }
                    break;
                }
                default:{
                    return  $this->help($args);
                }
            }
            if(empty($list)){
                return 'not process was kill';
            }else{
                if($option == '-f'){
                    $sig = 9;
                }else{
                    $sig = 15;
                }
                $ret = '';
                foreach ($list as $pid){
                    \Swoole\Process::kill($pid,$sig);
                    $ret .= Utility::displayItem('kill pid :',$pid.' '.$option)."\n";
                }
                return $ret;
            }
        }else{
            return "there is not process info";
        }
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo."
php easyswoole process kill PID
php easyswoole process kill PID -f
php easyswoole process kill GroupName -f
php easyswoole process killAll
php easyswoole process killAll -f
";
    }
}