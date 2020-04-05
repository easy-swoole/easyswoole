<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\BaseService\BaseService;
use EasySwoole\EasySwoole\BaseService\Exception;
use EasySwoole\EasySwoole\BaseService\Package;
use EasySwoole\EasySwoole\BaseService\UnixSocket;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Task implements CommandInterface
{

    public function commandName(): string
    {
        return 'task';
    }

    public function exec(array $args): ?string
    {
        $action = array_shift($args);
        switch ($action) {
            case 'status':
                $result = $this->status();
                break;
            default:
                $result = $this->help($args);
                break;
        }
        return $result;
    }

    protected function status()
    {
        try {
            $package = new Package();
            $package->setOperation($package::OP_TASK_INFO);
            $data =  UnixSocket::unixSocketSendAndRecv(BaseService::$baseServiceSockFile,$package);
            if (empty($data)) {
                return "task info is abnormal";
            }
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
        $result = new  ArrayToTextTable($data);

        return $result;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole task status
";
    }
}