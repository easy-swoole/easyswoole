<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/6
 * Time: 下午4:18
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Communicate\SysCommand;
use EasySwoole\Core\Component\Event;
use EasySwoole\Core\Component\Cluster\Communicate\CommandBean;
use EasySwoole\Core\Component\Cluster\Server\ServerManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;

class CommandRegister extends Event
{
    use Singleton;

    function __construct(array $allowKeys = null)
    {
        parent::__construct($allowKeys);
        $this->set(SysCommand::NODE_BROADCAST,function (CommandBean $commandBean,$udpAddress){
            //广播自身节点

        });

        $this->set(SysCommand::RPC_NODE_BROADCAST,function (CommandBean $commandBean,$udpAddress){
           $list = $commandBean->getArgs();
           if(is_array($list)){
               foreach ($list as $item){
                    $node = new ServiceNode($item);
                    ServiceManager::getInstance()->addServiceNode($node);
               }
           }
        });

        $this->set(SysCommand::NODE_SHUTDOWN, function (CommandBean $commandBean,$udpAddress) {

        });
    }

}