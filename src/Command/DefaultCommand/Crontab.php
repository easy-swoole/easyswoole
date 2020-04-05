<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\BaseService\BaseService;
use EasySwoole\EasySwoole\BaseService\Exception;
use EasySwoole\EasySwoole\BaseService\Package;
use EasySwoole\EasySwoole\BaseService\UnixSocket;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function exec(array $args): ?string
    {
        try{
            $action = array_shift($args);
            switch ($action) {
                case 'show':
                    $result = $this->show();
                    break;
                case 'stop':
                    $result = $this->stop($args);
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
        }catch (Exception $exception){
            return $exception->getMessage();
        }
        return $result;
    }

    protected function stop($args){
        $taskName = array_shift($args);
        $crontabTable = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        foreach ($crontabTable as $value){
            var_dump($value);
        }
//        var_dump($crontabTable);
//        $info = $crontabTable->get('test');
//        var_dump($info);
    }

    protected function show()
    {
        $package = new Package();
        $package->setOperation($package::OP_CRON_INFO);
        $data =  UnixSocket::unixSocketSendAndRecv(BaseService::$baseServiceSockFile,$package);
        if (empty($data)) {
            return "crontab info is abnormal";
        }
        foreach ($data as $k => $v) {
            $v['taskNextRunTime'] = date('Y-m-d H:i:s',$v['taskNextRunTime']);
            $data[$k] = array_merge(['taskName' => $k], $v);
        }
        return new ArrayToTextTable($data);
    }


    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole crontab show
php easyswoole crontab stop taskName
php easyswoole crontab resume taskName 
";
    }

}