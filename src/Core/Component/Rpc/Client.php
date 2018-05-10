<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午2:58
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Client\TaskObj;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\ServiceCaller;
use EasySwoole\Core\Component\Rpc\Client\ServiceResponse;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Common\ServiceNode;
use EasySwoole\Core\Component\Trigger;


class Client
{
    private $taskList = [];

    private $clientConnectTimeOut = 0.1;

    private $failNodes = [];

    function addCall(string $serviceName,string $serviceGroup,string $action,...$args)
    {
        $obj = new TaskObj();
        $obj->setServiceName($serviceName);
        $obj->setServiceAction($action);
        $obj->setServiceGroup($serviceGroup);
        $obj->setArgs($args);
        $this->taskList[] = $obj;
        return $obj;
    }


    function call($timeOut = 0.1)
    {
        $clients = [];
        $taskMap = [];
        $nodeMap = [];
        $this->failNodes = [];
        foreach ($this->taskList as $task){
            //获取节点
            $serviceNode = Server::getInstance()->getServiceOnlineNode($task->getServiceName());
            if($serviceNode instanceof ServiceNode){
                $client = $this->connect($serviceNode);
                if($client){
                    $client->send($this->buildData($serviceNode,$task));
                    $index = count($clients);
                    $clients[$index] = $client;
                    $taskMap[$index] = $task;
                    $nodeMap[$index] = $serviceNode;
                }else{
                    $response = new ServiceResponse($task->toArray()+['status'=>Status::CLIENT_CONNECT_FAIL]);
                    $this->callFunc($response,$task);
                    $this->failNodes[] = $serviceNode;
                }
            }else{
                $response = new ServiceResponse($task->toArray()+['status'=>Status::CLIENT_SERVER_NOT_FOUND]);
                $this->callFunc($response,$task);
            }
        }

        //全部执行调度
        $startTime  = microtime(true);
        while (!empty($clients)){
            $write = $error = array();
            $read = $clients;
            $n = swoole_client_select($read, $write, $error, 0.01);
            if($n > 0){
                foreach ($read as $index =>$client){
                    $msg = $client->recv();
                    $node = $nodeMap[$index];
                    $response = $this->decodeData($node,$msg);
                    $this->callFunc($response,$taskMap[$index]);
                    $client->close();
                    unset($clients[$index]);
                }
            }
            $now = microtime(1);
            $spend = round($now-$startTime,4);
            //服务端超时响应自动重试暂未处理
            if($spend > $timeOut){
                foreach ($clients as $index => $client){
                    $node = $nodeMap[$index];
                    $this->failNodes[] = $node;
                    $response = $this->decodeData($node,'');
                    $this->callFunc($response,$taskMap[$index]);
                    $client->close();
                    unset($clients[$index]);
                }
            }
        }
    }

    function getFailNodes():array
    {
        return $this->failNodes;
    }

    private function callFunc(ServiceResponse $obj,TaskObj $taskObj)
    {
        if($obj->getStatus() === Status::OK){
            $func = $taskObj->getSuccessCall();
        }else{
            $func = $taskObj->getFailCall();
        }
        if(is_callable($func)){
            try{
                Invoker::callUserFunc($func,$obj);
            }catch (\Throwable $exception){
                Trigger::throwable($exception);
            }
        }
    }

    private function connect(ServiceNode $node): ?\swoole_client
    {
        $client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
        $client->set([
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 1024 * 64
        ]);
        if ($client->connect($node->getAddress(), $node->getPort(),$this->clientConnectTimeOut)) {
            return $client;
        } else {
            $client->close();
            return null;
        }
    }

    private function buildData(ServiceNode $node,TaskObj $taskObj)
    {
        $data = (new ServiceCaller($taskObj->toArray()))->__toString();
        if(!empty($node->getEncryptToken())){
            $openssl = new Openssl($node->getEncryptToken());
            $data = $openssl->encrypt($data);
        }
        return Parser::pack($data);
    }

    private function decodeData(ServiceNode $node,?string $raw):ServiceResponse
    {
        $raw = Parser::unPack($raw);
        if(!empty($node->getEncryptToken())){
            $openssl = new Openssl($node->getEncryptToken());
            $raw = $openssl->decrypt($raw);
            if($raw === false){
                $json = [
                    'status'=>Status::PACKAGE_ENCRYPT_DECODED_ERROR
                ];
            }
        }
        //如果已经解密失败,则不再做包解析
        if(!isset($json)){
            $json = json_decode($raw,true);
        }
        //若包解析失败
        if(!is_array($json)){
            $json = [
                'status'=>Status::CLIENT_WAIT_RESPONSE_TIMEOUT
            ];
        }
        return new ServiceResponse( $json + ['responseNode'=>$node]);
    }
}