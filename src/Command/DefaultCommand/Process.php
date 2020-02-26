<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

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
         * php easyswoole process show
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
                    foreach ($json as $pid => $value){
                        if(is_numeric($pidOrGroupName)){
                            if($value['pid'] == $pidOrGroupName){
                                $list[$pid] = $value;
                            }
                        }else{
                            if($value['group'] == $pidOrGroupName){
                                $list[$pid] = $value;
                            }
                        }

                    }
                    break;
                }
                case 'killAll':{
                    $option = array_shift($args);
                    $list = $json;
                    break;
                }
                case 'show':{
                    return new ArrayToTextTable($json);
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
                    $option = 'SIGKILL';

                }else{
                    $sig = 15;
                    $option = 'SIGTERM';
                }
                foreach ($list as $pid => $value){
                    \Swoole\Process::kill($pid,$sig);
                    $list[$pid]['option'] = $option;
                }
                return new ArrayToTextTable($list);
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
php easyswoole process show
";
    }
}