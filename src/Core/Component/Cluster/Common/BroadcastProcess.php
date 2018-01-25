<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/25
 * Time: 上午10:08
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\Component\Cluster\NetWork\Udp;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;

class BroadcastProcess extends AbstractProcess
{
    private $listener = null;
    public function run(Process $process)
    {
        // TODO: Implement run() method.
        $this->listen();
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.

    }

    public function onReceive(string $str,...$args)
    {
        // TODO: Implement onReceive() method.
        var_dump($args);
    }

    private function createListen($port,$address = '0.0.0.0')
    {
        if($this->listener){
            swoole_event_del($this->listener);
            fclose($this->listener);
            $this->listener = null;
        }
        try{
            $this->listener =  Udp::listen($port,$address);
            return $this->listener;
        }catch (\Throwable $throwable){
           throw $throwable;
        }
    }

    private function listen()
    {
        try{
            $this->createListen(Config::getInstance()->get('listenPort'),Config::getInstance()->get('listenAddress'));
            swoole_event_add($this->listener,function($listen){
                $data = stream_socket_recvfrom($listen,8192,0,$address);
                $this->onReceive($data);
            });
        }catch (\Throwable $exception){
            trigger_error('fail to start  broadcast listener');
        }
    }
}