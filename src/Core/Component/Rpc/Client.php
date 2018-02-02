<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午2:58
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\Component\Rpc\Client\ResponseObj;
use EasySwoole\Core\Component\Rpc\Client\TaskObj;
use EasySwoole\Core\Component\Rpc\Common\Command;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
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
                                    $commandBean = new CommandBean();
                                    $commandBean->setArgs($task->getArgs());
                                    //controllerClass作为服务名称
                                    $commandBean->setControllerClass($node->getServiceName());
                                    $commandBean->setAction($task->getServiceAction());
                                    $data = $encoder->encodeRawData($commandBean->__toString());
                                    $clients[$index]->send($data);
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
                        $commandBean = new CommandBean();
                        $commandBean->setArgs($task->getArgs());
                        //controllerClass作为服务名称
                        $commandBean->setControllerClass($node->getServiceName());
                        $commandBean->setAction($task->getServiceAction());
                        $data = $encoder->encodeRawData($commandBean->__toString());
                        $clients[$index]->send($data);
                        $map[$index] = $task;
                        $nodeMap[$index] = $node;
                    }
                }else{
                    $res = new ResponseObj();
                    $res->setAction("{$task->getServiceName()}@{$task->getServiceAction()}");
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
                    $data = $client->recv();
                    $data = json_decode($encoder->decodeRawData($data),true);
                    $data = $data ?: [];
                    $res = new ResponseObj($data);
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
                call_user_func($func,$obj);
            }catch (\Throwable $exception){
                trigger_error($exception->getMessage().'@'.$exception->getTraceAsString());
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
}