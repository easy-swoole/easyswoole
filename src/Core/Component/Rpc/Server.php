<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午11:38
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Rpc\Client\ResponseObj;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Swoole\ServerManager;

class Server
{
    use Singleton;
    private $list = [];

    /**
     * @param string $name
     * @param string $serviceClass
     * @param mixed $encrypt false,or openssl method ,like DES-EDE3
     * @return $this|bool
     */
    function addService(string $name, string $serviceClass, $encrypt = false)
    {
        //一个EasySwoole服务上不允许同名服务
        $this->list[$name] = $serviceClass;
        return $this;
    }

    public function attach(int $port,string $address = '0.0.0.0')
    {
        foreach ($this->list as $name => $item){
            $node = new ServiceNode();
            $node->setPort($port);
            $node->setServiceName($name);
            ServiceManager::getInstance()->addServiceNode($node);
        }

        $sub = ServerManager::getInstance()->addServer('RPC',$port,SWOOLE_TCP,$address,[
            'open_length_check' => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
            'package_max_length'    => 1024*64,
            'heartbeat_idle_time' => 15,
            'heartbeat_check_interval' => 2,
        ]);

        $sub->registerDefaultOnReceive(new Parser($this->list),function ($err){
            $bean = new ResponseObj();
            $bean->setError($err);
            $bean->setStatus(Status::ACTION_NOT_FOUND);
            return $bean->__toString();
        });

    }
}