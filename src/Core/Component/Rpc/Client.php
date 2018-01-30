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

    public function call($timeOut = 0.1)
    {
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
                    $clients[$index] = new \swoole_client(SWOOLE_TCP,SWOOLE_SOCK_SYNC);
                    $clients[$index]->set([
                        'open_length_check' => true,
                        'package_length_type'   => 'N',
                        'package_length_offset' => 0,
                        'package_body_offset'   => 4,
                        'package_max_length'    => 1024*64
                    ]);
                    if (! $clients[$index]->connect($node->getAddress(), $node->getPort())) {
                        $res = new ResponseObj();
                        $res->setServiceNode($node);
                        $res->setStatus(Status::CONNECT_FAIL);
                        $res->setAction($task->getServiceAction());
                        $this->callFunc($res,$task);
                        $clients[$index]->close();
                        unset($clients[$index]);
                    }else{
                        $commandBean = new CommandBean();
                        $commandBean->setArgs($task->getArgs());
                        //controllerClass作为服务名称
                        $commandBean->setControllerClass($node->getServiceName());
                        $commandBean->setAction($task->getServiceAction());
                        $sendStr = \swoole_serialize::pack($commandBean);
                        $data = pack('N', strlen($sendStr)).$sendStr;
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
                    $resp = substr($data, 4);
                    $commandBean = \swoole_serialize::unpack($resp);
                    $res = new ResponseObj($commandBean->toArray());
                    $res->setAction($map[$index]->getServiceAction());
                    $res->setServiceNode($nodeMap[$index]);
                    $this->callFunc($res,$map[$index]);
                    $client->close();
                    unset($clients[$index]);
                }
            }
            $now = microtime(1);
            $spend = round($now-$startTime,4);
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
}