<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午5:55
 */

namespace Core\Component\RPC\Client;


use Core\Component\RPC\Common\AbstractPackageDecoder;
use Core\Component\RPC\Common\AbstractPackageEncoder;
use Core\Component\RPC\Common\Config;
use Core\Component\RPC\Common\Package;

class Client
{
    protected $serverList = [];
    function selectServer(Config $serverInfo){
        if(empty($serverInfo->getHost())){
            throw new \Exception("rpc host error@".$serverInfo->getHost());
        }
        if(empty($serverInfo->getPort())){
            throw new \Exception("rpc host port error@".$serverInfo->getPort());
        }
        $serverHash = spl_object_hash($serverInfo);
        if(isset($this->serverList[$serverHash])){
            return $this->serverList[$serverHash];
        }else{
            $call = new TaskList($serverInfo);
            $this->serverList[$serverHash] = $call;
            return $call;
        }
    }

    function run($timeOut = 1000){
        $clients = array();
        $mapInfo = array();
        foreach ($this->serverList as $item){
            if($item instanceof TaskList){
                $taskList = $item->getTaskList();
                $taskServerConf = $item->getConfig();
                foreach ($taskList as $task){
                    if($task instanceof TaskObj){
                        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
                        $client->set(array(
                            'open_eof_check'=>true,
                            'package_eof'=>$taskServerConf->getEof(),//\r\n
                        ));
                        $client->connect($taskServerConf->getHost(), $taskServerConf->getPort(), $taskServerConf->getConnectTimeOut(), 0);
                        if($client->isConnected()){
                            $data = $task->getPackage()->__toString();
                            $encoder = $taskServerConf->getPackageEncoder();
                            if($encoder instanceof AbstractPackageEncoder){
                                $data = $encoder->encode($data);
                            }
                            $client->send($data.$taskServerConf->getEof());
                            $clients[$client->sock] = $client;
                            $mapInfo[$client->sock] = array(
                                'taskObj'=>$task,
                                'eof'=>$taskServerConf->getEof(),
                                'decoder'=>$taskServerConf->getPackageDecoder()
                            );
                        }else{
                            $handler = $task->getFailCall();
                            if(is_callable($handler)){
                                call_user_func($handler,$task->getPackage());
                            }
                        }
                    }
                }
            }
        }
        $start = microtime(1);
        while (!empty($clients))
        {
            $write = $error = array();
            $read = array_values($clients);
            $n = swoole_client_select($read, $write, $error, 0.1);
            if ($n > 0)
            {
                foreach ($read as $index => $c)
                {
                    $data = $c->recv();
                    $eof = $mapInfo[$c->sock]['eof'];
                    $data = substr($data,0,-strlen($eof));
                    $decoder = $mapInfo[$c->sock]['decoder'];
                    if($decoder instanceof AbstractPackageDecoder){
                        $data = $decoder->decode($data);
                    }
                    $arr = json_decode($data,1);
                    $arr = is_array($arr) ? $arr :[];
                    $package = new Package($arr);
                    $handler = $mapInfo[$c->sock]['taskObj']->getSuccessCall();
                    if(is_callable($handler)){
                        call_user_func($handler,$package);
                    }
                    $c->close();
                    unset($clients[$c->sock]);
                    unset($mapInfo[$c->sock]);
                }
            }
            $now = microtime(1);
            $spend = intval(($now-$start)*1000);
            if($spend > $timeOut){
                foreach ($clients as $sock =>$client){
                    $handler = $mapInfo[$sock]['taskObj']->getFailCall();
                    if(is_callable($handler)){
                        call_user_func($handler,$mapInfo[$sock]['taskObj']->getPackage());
                    }
                    $client->close();
                    unset($clients[$client->sock]);
                    unset($mapInfo[$client->sock]);
                }
                break;
            }
        }
    }
}