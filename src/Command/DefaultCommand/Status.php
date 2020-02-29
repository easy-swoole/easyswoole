<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Status implements CommandInterface
{

    public function commandName(): string
    {
        return  "status";
    }

    public function exec(array $args): ?string
    {
        $file = EASYSWOOLE_TEMP_DIR.'/status.json';
        if(is_file($file)){
            $data = file_get_contents($file);
            $data = json_decode($data,true);
            $data['start_time'] = date('Y-m-d h:i:s',$data['start_time']);
            $ret = '';
            foreach ($data as $key => $val){
                $ret .= Utility::displayItem($key,$val)."\n";
            }
            return $ret;
        }else{
            return  'not server status info';
        }
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . <<<HELP
php easyswoole server status
HELP;
    }
}