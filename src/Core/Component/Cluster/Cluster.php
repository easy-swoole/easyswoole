<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午10:51
 */

namespace EasySwoole\Core\Component\Cluster;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\ServerManager;

class Cluster
{
    use Singleton;

    private $currentNode;

    function __construct()
    {
        $conf = Config::getInstance()->getConf('CLUSTER');
        $this->currentNode = new NodeBean($conf);
    }

    function run()
    {
        if($this->currentNode->getEnable()){
            $name = Config::getInstance()->getConf('SERVER_NAME');
            foreach ($this->currentNode->getListenAddress() as $address){
                $address = explode(':',$address);
                $listen = array_shift($address);
                $port =  array_shift($address);
                $sub = ServerManager::getInstance()->addServer("{$name}_Cluster",$port,SWOOLE_UDP,$listen);
                EventHelper::register($sub,$sub::onPacket,function (\swoole_server $server, string $data, array $client_info){

                });
            }
        }
    }

    function currentNode():NodeBean
    {
        return $this->currentNode;
    }

}