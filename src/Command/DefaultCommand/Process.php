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
            $pidOrGroupName = array_shift($args);
            $option = array_shift($args);
            $json = json_decode(file_get_contents($file),true);
            switch ($action){
                case 'kill':{
                    break;
                }
                case 'killAll':{
                    break;
                }
            }
            return  $this->help($args);
        }else{
            return "there is not process info";
        }
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo;
    }
}