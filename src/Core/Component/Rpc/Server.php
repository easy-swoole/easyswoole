<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午11:38
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\ServiceResponse;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Response;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\ServerManager;

class Server
{
    use Singleton;

    private $list = [];
    private $controllerNameSpace = 'App\\RpcController\\';
    private $protocolSetting = [
        'open_length_check' => true,
        'package_length_type'   => 'N',
        'package_length_offset' => 0,
        'package_body_offset'   => 4,
        'package_max_length'    => 1024*64,
        'heartbeat_idle_time' => 5,
        'heartbeat_check_interval' => 30,
    ];

    function setProtocolSetting(array $data)
    {
        $this->protocolSetting = $data;
        return $this;
    }

    function setControllerNameSpace(string $nameSpace):Server
    {
        $this->controllerNameSpace = $nameSpace;
        return $this;
    }

    function addService(string $serviceName,int $port,$encryptToken = null,string $address = '0.0.0.0')
    {
        //一个EasySwoole服务上不允许同名服务
        $this->list[$serviceName] = [
            'serviceName'=>$serviceName,
            'port'=>$port,
            'encryptToken'=>$encryptToken,
            'address'=>$address
        ];
        return $this;
    }

    public function attach()
    {
        foreach ($this->list as $name => $item){
            $node = new ServiceNode();
            $node->setPort($item['port']);
            $node->setServiceName($name);
            $node->setEncryptToken($item['encryptToken']);
            ServiceManager::getInstance()->addServiceNode($node);

            $sub = ServerManager::getInstance()->addServer("RPC_SERVER_{$name}",$item['port'],SWOOLE_TCP,$item['address'],$this->protocolSetting);

            $nameSpace = $this->controllerNameSpace.ucfirst($item['serviceName']);
            EventHelper::register($sub,$sub::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data)use($item,$nameSpace){
                $response = new ServiceResponse();
                $client = new Tcp($fd,$reactor_id);
                $data = Parser::unPack($data);
                $openssl = null;
                if(!empty($item['encryptToken'])){
                    $openssl = new Openssl($item['encryptToken']);
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
}