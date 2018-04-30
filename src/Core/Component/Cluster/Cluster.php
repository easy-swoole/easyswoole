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
use EasySwoole\Core\Component\Cluster\Callback\BroadcastCallbackContainer;
use EasySwoole\Core\Component\Cluster\Callback\DefaultCallbackName;
use EasySwoole\Core\Component\Cluster\Callback\NodeOffLienCallbackContainer;
use EasySwoole\Core\Component\Cluster\Callback\ShutdownCallBackContainer;
use EasySwoole\Core\Component\Cluster\Common\BaseServiceProcess;
use EasySwoole\Core\Component\Cluster\Common\MessageBean;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;
use EasySwoole\Core\Component\Cluster\Callback\MessageCallbackContainer;
use EasySwoole\Core\Component\Cluster\NetWork\Deliverer;
use EasySwoole\Core\Component\Cluster\NetWork\PacketParser;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Common\ServiceNode;
use EasySwoole\Core\Component\Rpc\Server;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Table;

class Cluster
{
    use Singleton;

    private $currentNode;

    function __construct()
    {
        $conf = Config::getInstance()->getConf('CLUSTER');
        $this->currentNode = new NodeBean($conf);
        if($this->currentNode->getEnable() && empty($this->currentNode->getToken())){
            Trigger::throwable(new \Exception('cluster token could not be empty and set cluster mode disable automatic'));
            $this->currentNode->setEnable(false) ;
        }
        if($this->currentNode->getEnable() && empty($this->currentNode->getListenAddress())){
            Trigger::throwable(new \Exception('cluster listenAddress could not be empty and set cluster mode disable automatic'));
            $this->currentNode->setEnable(false) ;
        }
        TableManager::getInstance()->add('ClusterNodeList',[
            'nodeName'=>[
                'type'=>Table::TYPE_STRING,'size'=>20
            ],
            'udpAddress'=>[
                'type'=>Table::TYPE_STRING,'size'=>16
            ],
            'udpPort'=>[
                'type'=>Table::TYPE_INT,'size'=>10
            ],
            'listenPort'=>[
                'type'=>Table::TYPE_STRING,'size'=>10
            ],
            'lastBeatBeatTime'=>[
                'type'=>Table::TYPE_INT,'size'=>10
            ]
        ]);
    }

    function run()
    {
        if($this->currentNode->getEnable()){
            self::registerDefaultCallback();
            $name = Config::getInstance()->getConf('SERVER_NAME');
            ProcessManager::getInstance()->addProcess("{$name}_Cluster_BaseService",BaseServiceProcess::class,['currentNode'=>$this->currentNode]);
            $sub = ServerManager::getInstance()->addServer("{$name}_Cluster",$this->currentNode->getListenPort(),SWOOLE_SOCK_UDP,$this->currentNode->getListenAddress());
            $openssl = new Openssl($this->currentNode->getToken());
            EventHelper::register($sub,$sub::onPacket,function (\swoole_server $server, string $data, array $client_info)use($openssl){
                $data = $openssl->decrypt($data);
                $udpClient = new Udp($client_info);
                $message = PacketParser::unpack((string)$data,$udpClient);
                if($message){
                    MessageCallbackContainer::getInstance()->hook($message->getCommand(),$message);
                }
            });
        }
    }

    function currentNode():NodeBean
    {
        return $this->currentNode;
    }

    function allNodes():array
    {
        $ret = [];
        $list = TableManager::getInstance()->get('ClusterNodeList');
        $time = time();
        $ttl = $this->currentNode->getNodeTimeout();
        foreach ($list as $key => $item){
            $node = new NodeBean([
                'nodeId'=>$key,
                'nodeName'=>$item['nodeName'],
                'lastBeatBeatTime'=>$item['lastBeatBeatTime'],
                'udpInfo'=>[
                    'address'=>$item['udpAddress'],
                    'port'=>$item['udpPort']
                ],
                'listenPort'=>$item['listenPort']
            ]);
            if($time - $item['lastBeatBeatTime'] > $ttl){
                NodeOffLienCallbackContainer::getInstance()->call($node,false);
                TableManager::getInstance()->get('ClusterNodeList')->del($key);
            }else{
                $ret[] = $node;
            }
        }
        return $ret;
    }

    function getNode($nodeId):?NodeBean
    {
        $item = TableManager::getInstance()->get('ClusterNodeList')->get($nodeId);
        if(is_array($item)){
            $ttl = $this->currentNode->getNodeTimeout();
            $node = new NodeBean([
                'nodeId'=>$nodeId,
                'nodeName'=>$item['nodeName'],
                'lastBeatBeatTime'=>$item['lastBeatBeatTime'],
                'udpInfo'=>[
                    'address'=>$item['udpAddress'],
                    'port'=>$item['udpPort']
                ],
                'listenPort'=>$item['listenPort']
            ]);
            if(time() - $item['lastBeatBeatTime'] > $ttl){
                NodeOffLienCallbackContainer::getInstance()->call($node,false);
                return null;
            }else{
                return $node;
            }
        }else{
            return null;
        }
    }
    /*
     * 注册默认服务
     */
    private static function registerDefaultCallback()
    {
        //集群节点广播回调
        MessageCallbackContainer::getInstance()->add(DefaultCallbackName::CLUSTER_NODE_BROADCAST,function (MessageBean $messageBean){
            $node = $messageBean->getFromNode();
            TableManager::getInstance()->get('ClusterNodeList')->set($node->getNodeId(),[
                'nodeName'=>$node->getNodeName(),
                'udpAddress'=>$node->getUdpInfo()->getAddress(),
                'udpPort'=>$node->getUdpInfo()->getPort(),
                'lastBeatBeatTime'=>time(),
                'listenPort'=>$node->getListenPort()
            ]);
        });
        //集群节点广播关机回调
        MessageCallbackContainer::getInstance()->add(DefaultCallbackName::CLUSTER_NODE_SHUTDOWN,function (MessageBean $messageBean){
            $node = $messageBean->getFromNode();
            TableManager::getInstance()->get('ClusterNodeList')->del($node->getNodeId());
            //下线该服务的全部rpc服务
            Server::getInstance()->serverNodeOffLine($node);
            NodeOffLienCallbackContainer::getInstance()->call($node,true);
        });
        //RPC服务节点广播回调
        MessageCallbackContainer::getInstance()->add(DefaultCallbackName::RPC_SERVICE_BROADCAST,function (MessageBean $messageBean){
            $node = $messageBean->getFromNode();
            $list = $messageBean->getArgs();
            foreach ($list as $item){
                $serviceNode = new ServiceNode($item);
                //可达主机地址即为udp地址（真实地址）
                $serviceNode->setAddress($node->getUdpInfo()->getAddress());
                Server::getInstance()->updateServiceNode($serviceNode);
            }
        });

        //集群节点广播
        BroadcastCallbackContainer::getInstance()->set(DefaultCallbackName::CLUSTER_NODE_BROADCAST,function (){
            $message = new MessageBean();
            $message->setCommand(DefaultCallbackName::CLUSTER_NODE_BROADCAST);
            Deliverer::broadcast($message);
        });
        //RPC服务广播
        BroadcastCallbackContainer::getInstance()->set(DefaultCallbackName::RPC_SERVICE_BROADCAST,function (){
            $ret = Server::getInstance()->allLocalServiceNodes();
            $data = [];
            foreach ($ret as $item){
                $data[] = $item->toArray();
            }
            $message = new MessageBean();
            $message->setArgs($data);
            $message->setCommand(DefaultCallbackName::RPC_SERVICE_BROADCAST);
            Deliverer::broadcast($message);
        });
        //注册默认集群关机回调
        ShutdownCallBackContainer::getInstance()->set(DefaultCallbackName::CLUSTER_NODE_SHUTDOWN,function (){
            $message = new MessageBean();
            $message->setCommand(DefaultCallbackName::CLUSTER_NODE_SHUTDOWN);
            Deliverer::broadcast($message);
        });
    }

}