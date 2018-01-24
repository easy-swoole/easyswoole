<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午11:38
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Swoole\ServerManager;

class Server
{
    use Singleton;
    private $list = [];
    function addService(string $name,int $port,string $serviceClass ,string $address = '0.0.0.0')
    {
        $node = new ServiceNode();
        $node->setPort($port);
        $node->setServiceName($name);
        ServiceManager::getInstance()->addServiceNode($node);
        //一个EasySwoole服务上不允许同名服务
        $this->list[$name] = [
            'address'=>$address,
            'port'=>$port,
            'serviceClass'=>$serviceClass
        ];
    }

    public function attach()
    {
        foreach ($this->list as $name => $item){
            $sub = ServerManager::getInstance()->addServer($name,$item['port'],SWOOLE_TCP,$item['address'],[
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*64,
                'heartbeat_idle_time' => 15,
                'heartbeat_check_interval' => 2,
            ]);
            $sub->registerDefaultOnReceive(new Parser($item['serviceClass']));
        }
    }
}