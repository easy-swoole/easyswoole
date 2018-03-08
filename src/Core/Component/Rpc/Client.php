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
use EasySwoole\Core\Component\Rpc\Client\ResponseObj;
use EasySwoole\Core\Component\Rpc\Client\TaskObj;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\Common\CommandBean;

class Client
{
    private $taskList = [];
    function addCall(string $serviceName,string $action,...$args)
    {
        $obj = new TaskObj();
        $obj->setServiceName($serviceName);
        $obj->setServiceAction($action);
        $obj->setArgs($args);
        $this->taskList[] = $obj;
        return $obj;
    }

    /*
     * 当开启自动重试，对连接失败或者是超时的任务，进行获取另外一个服务节点二次尝试，若不存在其他服务节点则判定失败
     */
    public function call($timeOut = 0.1, $reTry = false)
    {
        $encoder = new Parser([]);
        $map = [];
        $clients = [];
        $nodeMap = [];
        foreach ($this->taskList as $task){
            if($task instanceof TaskObj){
                if($task->getServiceId()){
                    //若指定了节点
                    $node = ServiceManager::getInstance()->getServiceNodeById($task->getServiceId());
                }else{
                    $node = ServiceManager::getInstance()->getServiceNode($task->getServiceName());
                }
                if($node instanceof ServiceNode){
                    $index = count($clients);
                    $client = $this->connect($node);
                    if (!$client) {
                        //若未指定节点   尝试再去获取一个节点
                        if ($reTry && empty($task->getServiceId())) {
                            $node = ServiceManager::getInstance()->getServiceNode($task->getServiceName(), $node->getServerId());
                            if ($node) {
                                $client = $this->connect($node);
                                if (!$client) {
                                    $res = new ResponseObj();
                                    $res->setServiceNode($node);
                                    $res->setStatus(Status::CONNECT_FAIL);
                                    $res->setAction($task->getServiceAction());
                                    $this->callFunc($res, $task);
                                } else {
                                    $clients[$index] = $client;
                                    $clients[$index]->send($this->buildData($node,$task));
                                    $map[$index] = $task;
                                    $nodeMap[$index] = $node;
                                }
                            } else {
                                //因为仅存一个失败节点,因此判定为CONNECT_FAIL
                                $res = new ResponseObj();
                                $res->setServiceNode($node);
                                $res->setStatus(Status::CONNECT_FAIL);
                                $res->setAction($task->getServiceAction());
                                $this->callFunc($res, $task);
                            }
                        } else {
                            $res = new ResponseObj();
                            $res->setServiceNode($node);
                            $res->setStatus(Status::CONNECT_FAIL);
                            $res->setAction($task->getServiceAction());
                            $this->callFunc($res, $task);
                        }
                    }else{
                        $clients[$index] = $client;
                        $clients[$index]->send($this->buildData($node,$task));
                        $map[$index] = $task;
                        $nodeMap[$index] = $node;
                    }
                }else{
                    $res = new ResponseObj();
                    $node = new ServiceNode();
                    $node->setServiceName($task->getServiceName());
                    $res->setAction($task->getServiceAction());
                    $res->setServiceNode($node);
                    $res->setStatus(Status::SERVICE_NOT_FOUND);
                    $this->callFunc($res,$task);
                }
            }
        }
        //进行全部调度
        $startTime  = microtime(true);
        while (!empty($clients)){
            $write = $error = array();
            $read = $clients;
            $n = swoole_client_select($read, $write, $error, 0.01);
            if($n > 0){
                foreach ($read as $index =>$client){
                    $msg = $client->recv();
                    $node = $nodeMap[$index];
                    $data = json_decode($this->decodeData($node,$msg),true);
                    if(is_array($data)){
                        $res = new ResponseObj($data);
                    }else{
                        $res = new ResponseObj([]);
                        $res->setError($msg);
                    }
                    $res->setAction($map[$index]->getServiceAction());
                    $res->setServiceNode($nodeMap[$index]);
                    $this->callFunc($res,$map[$index]);
                    $client->close();
                    unset($clients[$index]);
                }
            }
            $now = microtime(1);
            $spend = round($now-$startTime,4);
            //服务端超时响应自动重试暂未处理
            if($spend > $timeOut){
                foreach ($clients as $index => $client){
                    $res = new ResponseObj();
                    $res->setStatus(Status::TIMEOUT);
                    $res->setAction($map[$index]->getServiceAction());
                    $res->setServiceNode($nodeMap[$index]);
                    $this->callFunc($res,$map[$index]);
                    $client->close();
                    unset($clients[$index]);
                }
            }
        }
    }

    private function callFunc(ResponseObj $obj,TaskObj $taskObj)
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
        if ($client->connect($node->getAddress(), $node->getPort())) {
            return $client;
        } else {
            $client->close();
            return null;
        }
    }

    private function buildData(ServiceNode $node,TaskObj $taskObj)
    {
        $commandBean = new CommandBean();
        $commandBean->setArgs($taskObj->getArgs());
        //controllerClass作为服务名称
        $commandBean->setControllerClass($node->getServiceName());
        $commandBean->setAction($taskObj->getServiceAction());
        $data = $commandBean->__toString();
        //在swoole table中获取boolean出来的值，变为string
        if($node->getEncrypt()){
            $openssl = new Openssl($node->getToken(),$node->getEncrypt());
            $data = $openssl->encrypt($data);
        }
        return Parser::pack($data);
    }

    private function decodeData(ServiceNode $node,?string $raw)
    {
        $raw = Parser::unPack($raw);
        if($node->getEncrypt()){
            $openssl = new Openssl($node->getToken(),$node->getEncrypt());
            $raw = $openssl->encrypt($raw);
        }
        return $raw;
    }
}