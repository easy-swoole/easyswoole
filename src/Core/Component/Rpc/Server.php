<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午11:38
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Cluster;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\ServiceResponse;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Common\ServiceNode;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Response;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Table;

class Server
{
    use Singleton;

    function __construct()
    {
        TableManager::getInstance()->add('__RpcService',[
            'serviceName'=>[
                'size'=>35,
                'type'=>Table::TYPE_STRING
            ],
            'serverNodeId'=>[
                'size'=>16,
                'type'=>Table::TYPE_STRING
            ],
            'address'=>[
                'size'=>15,
                'type'=>Table::TYPE_STRING
            ],
            'port'=>[
                'size'=>10,
                'type'=>Table::TYPE_INT
            ],
            'lastHeartBeat'=>[
                'size'=>10,
                'type'=>Table::TYPE_INT
            ],
            'encryptToken'=>[
                'size'=>32,
                'type'=>Table::TYPE_STRING
            ]
        ],2048);
    }

    private $list = [];
    private $controllerNameSpace = 'App\\RpcController\\';

    function setControllerNameSpace(string $nameSpace):Server
    {
        $this->controllerNameSpace = $nameSpace;
        return $this;
    }

    function addService(string $serviceName,int $port,$encryptToken = null,string $address = '0.0.0.0')
    {
        //一个EasySwoole服务上不允许同名服务
        $this->list[$serviceName] = new ServiceNode([
            'serviceName'=>$serviceName,
            'port'=>$port,
            'encryptToken'=>$encryptToken,
            'address'=>$address
        ]);
        return $this;
    }

    /*
     * 获取全部在线的服务节点
     */
    function allOnlineServiceNodes():array
    {
        $ret = [];
        $table =  TableManager::getInstance()->get('__RpcService');
        $ttl = Cluster::getInstance()->currentNode()->getNodeTimeout();
        $time = time();
        foreach ($table as $key => $item){
            if($time - $item['lastHeartBeat'] > $ttl){
                $table->del($key);
            }else{
                $ret[] = new ServiceNode($item);
            }
        }
        return $ret;
    }

    /*
     * 获取某个服务的全部在线节点
     */
    function getServiceOnlineNodes($serviceName):array
    {
        $ret = [];
        $table =  TableManager::getInstance()->get('__RpcService');
        $ttl = Cluster::getInstance()->currentNode()->getNodeTimeout();
        $time = time();
        foreach ($table as $key => $item){
            if($time - $item['lastHeartBeat'] > $ttl){
                $table->del($key);
            }else if($item['serviceName'] == $serviceName){
                $ret[] = new ServiceNode($item);
            }
        }
        return $ret;
    }
    /*
     * 随机获取某个服务节点
     */
    function getServiceOnlineNode($serviceName):?ServiceNode
    {
        $list = $this->getServiceOnlineNodes($serviceName);
        if(!empty($list)){
            return $list[array_rand($list)];
        }else{
            return null;
        }
    }


    function allLocalServiceNodes():array
    {
        return $this->list;
    }

    function updateServiceNode(ServiceNode $serviceNode)
    {
        $serviceNode->setLastHeartBeat(time());
        TableManager::getInstance()->get('__RpcService')->set($this->generateKey($serviceNode),$serviceNode->toArray());
    }

    /*
     * 某个服务（器）节点完全下线
     */
    function serverNodeOffLine(NodeBean $nodeBean)
    {
        $table =  TableManager::getInstance()->get('__RpcService');
        foreach ($table as $key => $item){
            if($item['serverNodeId'] == $nodeBean->getNodeId()){
                $table->del($key);
            }
        }
    }


    public function attach($heartbeat_idle_time = 5,$heartbeat_check_interval = 30)
    {
        foreach ($this->list as $name => $node){

            $sub = ServerManager::getInstance()->addServer("RPC_SERVER_{$name}",$node->getPort(),SWOOLE_TCP,$node->getAddress(),[
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*64,
                'heartbeat_idle_time' => $heartbeat_idle_time,
                'heartbeat_check_interval' => $heartbeat_check_interval,
            ]);

            $nameSpace = $this->controllerNameSpace.ucfirst($name);
            EventHelper::register($sub,$sub::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data)use($node,$nameSpace){
                $response = new ServiceResponse();
                $client = new Tcp($fd,$reactor_id);
                $data = Parser::unPack($data);
                $openssl = null;
                if(!empty($node->getEncryptToken())){
                    $openssl = new Openssl($node->getEncryptToken());
                }
                if($openssl){
                    $data = $openssl->decrypt($data);
                }
                if($data !== false){
                    $caller = Parser::decode($data,$client);
                    if($caller){
                        $response->arrayToBean($caller->toArray());
                        $response->setArgs(null);
                        $group = ucfirst($caller->getServiceGroup());
                        $controller = "{$nameSpace}\\{$group}";
                        if(!class_exists($controller)){
                            $response->setStatus(Status::SERVICE_GROUP_NOT_FOUND);
                            $controller = "{$nameSpace}\\Index";
                            if(!class_exists($controller)){
                                $controller = null;
                            }else{
                                $response->setStatus(Status::OK);
                            }
                        }
                        if($controller){
                            try{
                                (new $controller($client,$caller,$response));
                            }catch (\Throwable $throwable){
                                Trigger::throwable($throwable);
                                $response->setStatus(Status::SERVICE_ERROR);
                            }
                        }else{
                            $response->setStatus(Status::SERVICE_NOT_FOUND);
                        }
                    }else{
                        $response->setStatus(Status::PACKAGE_DECODE_ERROR);
                    }
                }else{
                    $response->setStatus(Status::PACKAGE_ENCRYPT_DECODED_ERROR);
                }
                $response = json_encode($response->toArray(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                if($openssl){
                    $response =  $openssl->encrypt($response);
                }
                Response::response($client,Parser::pack($response));
            });
        }
    }

    private function generateKey(ServiceNode $serviceNode):string
    {
        return substr(md5($serviceNode->getServerNodeId().$serviceNode->getServiceName()), 8, 16);
    }
}