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
use EasySwoole\Core\Component\Cluster\Common\BaseServiceProcess;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;
use EasySwoole\Core\Component\Cluster\Event\MessageCallbackContainer;
use EasySwoole\Core\Component\Cluster\NetWork\PacketParser;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\Process\ProcessManager;
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
            ProcessManager::getInstance()->addProcess("{$name}_Cluster_BaseService",BaseServiceProcess::class,['currentNode'=>$this->currentNode]);
            foreach ($this->currentNode->getListenAddress() as $address){
                $address = explode(':',$address);
                $listen = array_shift($address);
                $port =  array_shift($address);
                $sub = ServerManager::getInstance()->addServer("{$name}_Cluster",$port,SWOOLE_UDP,$listen);
                $openssl = new Openssl($this->currentNode->getToken());
                EventHelper::register($sub,$sub::onPacket,function (\swoole_server $server, string $data, array $client_info)use($openssl){
                    $data = $openssl->decrypt($data);
                    $udpClient = new Udp($client_info);
                    $message = PacketParser::unpack((string)$data,$udpClient);
                    if($message){
                        $calls = MessageCallbackContainer::getInstance()->all();
                        foreach ($calls as $call){
                            try{
                                call_user_func($call,$message);
                            }catch (\Throwable $throwable){
                                Trigger::throwable($throwable);
                            }
                        }
                    }
                });
            }
        }
    }

    function currentNode():NodeBean
    {
        return $this->currentNode;
    }

}