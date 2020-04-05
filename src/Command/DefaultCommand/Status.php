<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\BaseService\BaseService;
use EasySwoole\EasySwoole\BaseService\Exception;
use EasySwoole\EasySwoole\BaseService\Package;
use EasySwoole\EasySwoole\BaseService\UnixSocket;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Status implements CommandInterface
{

    public function commandName(): string
    {
        return "status";
    }

    public function exec(array $args): ?string
    {
        try {
            $package = new Package();
            $package->setOperation($package::OP_SERVER_STATUS_INFO);
            $data =  UnixSocket::unixSocketSendAndRecv(BaseService::$baseServiceSockFile,$package);
            if (empty($data)) {
                return "server status info is abnormal";
            }
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
        $data['start_time'] = date('Y-m-d h:i:s', $data['start_time']);
        $ret = '';
        foreach ($data as $key => $val) {
            $ret .= Utility::displayItem($key, $val) . "\n";
        }
        return $ret;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . <<<HELP
php easyswoole server status
HELP;
    }
}