<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:36
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Swoole\EventHelper;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\Swoole\PipeMessage\Message;
use EasySwoole\EasySwoole\Swoole\PipeMessage\OnCommand;
use EasySwoole\EasySwoole\Swoole\Task\AbstractAsyncTask;
use EasySwoole\EasySwoole\Swoole\Task\SuperClosure;

class ServerManager
{
    use Singleton;

    private $swooleServer;
    private $mainServerEventRegister;

    private $subServer = [];
    private $subServerRegister = [];

    const TYPE_SERVER = 'server';
    const TYPE_WEB_SERVER = 'web_server';
    const TYPE_WEB_SOCKET_SERVER = 'ws_server';


    function __construct()
    {
        $this->mainServerEventRegister = new EventRegister();
    }
    /**
     * @param string $serverName
     * @return null|\swoole_server|\swoole_server_port
     */
    function getSwooleServer(string $serverName = null)
    {
        if($serverName === null){
            return $this->swooleServer;
        }else{
            if(isset($this->subServer[$serverName])){
                return $this->subServer[$serverName];
            }
            return null;
        }
    }

    function createSwooleServer($port,$type = self::TYPE_SERVER,$address = '0.0.0.0',array $setting = [],...$args):bool
    {
        switch ($type){
            case self::TYPE_SERVER:{
                $this->swooleServer = new \swoole_server($address,$port,...$args);
                break;
            }
            case self::TYPE_WEB_SERVER:{
                $this->swooleServer = new \swoole_http_server($address,$port,...$args);
                break;
            }
            case self::TYPE_WEB_SOCKET_SERVER:{
                $this->swooleServer = new \swoole_websocket_server($address,$port,...$args);
                break;
            }
            default:{
                Trigger::getInstance()->error('"unknown server type :{$type}"');
                return false;
            }
        }
        if($this->swooleServer){
            $this->swooleServer->set($setting);
        }
        $this->registerDefault($this->swooleServer);
        return true;
    }


    public function addServer(string $serverName,int $port,int $type = SWOOLE_TCP,string $host = '0.0.0.0',array $setting = [
        "open_eof_check"=>false,
    ]):EventRegister
    {
        $eventRegister = new EventRegister();
        $this->subServerRegister[$serverName] = [
            'port'=>$port,
            'host'=>$host,
            'type'=>$type,
            'setting'=>$setting,
            'eventRegister'=>$eventRegister
        ];
        return $eventRegister;
    }

    function getMainEventRegister():EventRegister
    {
        return $this->mainServerEventRegister;
    }

    function start()
    {
        $events = $this->getMainEventRegister()->all();
        foreach ($events as $event => $callback){
            $this->getSwooleServer()->on($event, function (...$args) use ($callback) {
                foreach ($callback as $item) {
                    call_user_func($item,...$args);
                }
            });
        }
        $this->attachListener();
        $this->getSwooleServer()->start();
    }

    private function attachListener():void
    {
        foreach ($this->subServerRegister as $serverName => $server){
            $subPort = $this->getSwooleServer()->addlistener($server['host'],$server['port'],$server['type']);
            if($subPort){
                $this->subServer[$serverName] = $subPort;
                if(is_array($server['setting'])){
                    $subPort->set($server['setting']);
                }
                $events = $server['eventRegister']->all();
                foreach ($events as $event => $callback){
                    $subPort->on($event, function (...$args) use ($callback) {
                        foreach ($callback as $item) {
                            call_user_func($item,...$args);
                        }
                    });
                }
            }else{
                Trigger::getInstance()->throwable(new \Exception("addListener with server name:{$serverName} at host:{$server['host']} port:{$server['port']} fail"));
            }
        }
    }


    private function registerDefault(\swoole_server $server)
    {
        //注册默认的on task,finish  不经过 event register。因为on task需要返回值。不建议重写onTask,否则es自带的异步任务事件失效
        EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj){
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $ret =  $taskObj->run($taskObj->getData(),$taskId,$fromWorkerId);
                    //在有return或者设置了结果的时候  说明需要执行结束回调
                    $ret = is_null($ret) ? $taskObj->getResult() : $ret;
                    if(!is_null($ret)){
                        $taskObj->setResult($ret);
                        return $taskObj;
                    }
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }else if($taskObj instanceof SuperClosure){
                try{
                    return $taskObj( $server, $taskId, $fromWorkerId);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
            return null;
        });
        EventHelper::on($server,EventRegister::onFinish,function (\swoole_server $server, $taskId, $taskObj){
            //finish 在仅仅对AbstractAsyncTask做处理，其余处理无意义。
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->finish($taskObj->getResult(),$taskId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }
        });

        //注册默认的pipe通讯
        OnCommand::getInstance()->set('TASK',function ($fromId,$taskObj){
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->run($taskObj->getData(),ServerManager::getInstance()->getSwooleServer()->worker_id,$fromId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }else if($taskObj instanceof SuperClosure){
                try{
                    $taskObj();
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
        });

        EventHelper::on($server,EventRegister::onPipeMessage,function (\swoole_server $server,$fromWorkerId,$data){
            $message = \swoole_serialize::unpack($data);
            if($message instanceof Message){
                OnCommand::getInstance()->hook($message->getCommand(),$fromWorkerId,$message->getData());
            }else{
                Trigger::getInstance()->error("data :{$data} not packet by swoole_serialize or not a Message Instance");
            }
        });
    }
}