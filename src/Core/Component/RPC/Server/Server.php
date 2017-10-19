<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午1:39
 */

namespace Core\Component\RPC\Server;

use Core\Component\RPC\Common\AbstractPackageDecoder;
use Core\Component\RPC\Common\AbstractPackageEncoder;
use Core\Component\RPC\Common\Config;
use Core\Component\RPC\Common\Package;
use Core\Component\Socket\Client\TcpClient;
use Core\Component\Socket\Response;
use Core\Swoole\AsyncTaskManager;
use Core\Swoole\Server as SwooleServer;

class Server
{
    protected $config;
    protected $serverList = [];
    function __construct(Config $bean)
    {
        $this->config = $bean;
    }

    function registerServer($serverName){
        if(isset($this->serverList[$serverName])){
            return $this->serverList[$serverName];
        }else{
            $handler = new ServerHandler();
            $this->serverList[$serverName] = $handler;
            return $handler;
        }
    }

    function attach($port,$listenAddress = '0.0.0.0'){
        $listener = SwooleServer::getInstance()->getServer()->addlistener($listenAddress,$port,SWOOLE_TCP);
        $listener->set(array(
            'heartbeat_check_interval'=>$this->config->getHeartBeatCheckInterval(),
            'open_eof_check'=>true,
            'package_eof'=>$this->config->getEof()
        ));
        $listener->on("receive",function(\swoole_server $server,$fd,$from_id,$data){
            $data = substr($data,0,-strlen($this->config->getEof()));
            $client = new TcpClient($server->getClientInfo($fd));
            $client->setFd($fd);
            $client->setReactorId($from_id);
            $parser = $this->config->getPackageDecoder();
            if($parser instanceof AbstractPackageDecoder){
                $data = $parser->decode($data);
            }
            $arr = json_decode($data,1);
            $arr = is_array($arr) ? $arr :[];
            $requestPackage = new Package($arr);
            $responsePackage = new Package();
            $serverName = $requestPackage->getServerName();
            $action = $requestPackage->getAction();
            if(isset($this->serverList[$serverName])){
                $handler = $this->serverList[$serverName]->getAction($action);
                $responsePackage->setServerName($serverName);
                $responsePackage->setAction($action);
            }else{
                $handler = null;
            }
            $eof = $this->config->getEof();
            if(is_callable($handler)){
                try{
                    $message = call_user_func_array($handler,array(
                        $responsePackage,$requestPackage,$client
                    ));
                    if($message !== null){
                        $responsePackage->setMessage($message);
                    }
                }catch (\Exception $exception){
                    $responsePackage->setErrorCode($responsePackage::ERROR_CODE_SERVER_ERROR);
                }
            }else{
                $responsePackage->setErrorCode($responsePackage::ERROR_CODE_ACTION_NOT_FOUND);
            }
            $resData = $responsePackage->__toString();
            $encoder = $this->config->getPackageEncoder();
            if($encoder instanceof AbstractPackageEncoder){
                $resData = $encoder->encode($resData);
            }
            AsyncTaskManager::getInstance()->add(function ()use($client,$resData,$eof){
                Response::response($client,$resData,$eof);
            });
        });
    }
}