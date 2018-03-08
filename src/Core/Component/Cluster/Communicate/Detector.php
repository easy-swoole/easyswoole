<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/30
 * Time: 下午5:35
 */

namespace EasySwoole\Core\Component\Cluster\Communicate;


use EasySwoole\Core\Component\Cluster\Common\EventRegister;
use EasySwoole\Core\Component\Cluster\Config;
use EasySwoole\Core\Component\Cluster\NetWork\Udp;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;

class Detector extends AbstractProcess
{
    /*
     * 用process+原生UDP为日后动态变更集群通讯端口留下方便
     */
    private $listener = null;

    public function run(Process $process)
    {
        EventRegister::getInstance()->hook(EventRegister::CLUSTER_START);
        $conf = Config::getInstance();
        $this->addTick($conf->getBroadcastTTL() * 1000, function ()use($conf) {
            if (!$this->listener) {
                $this->listen();
            }
            //广播自身节点
            $command = new CommandBean();
            $command->setCommand(SysCommand::NODE_BROADCAST);
            $command->setArgs($conf->toArray());
            Publisher::broadcast($command);
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
        EventRegister::getInstance()->hook(EventRegister::CLUSTER_SHUTDOWN);
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
        $str = Encrypt::getInstance()->getEncoder()->decrypt($str);
        $json = json_decode($str,true);
        if(is_array($json)){
            $command = new CommandBean($json);
            //仅仅需要一个UDP客户端
            EventRegister::getInstance()->hook(EventRegister::CLUSTER_ON_COMMAND,$command,array_shift($args));
        }
    }

    private function createListen($port, $address = '0.0.0.0')
    {
        if ($this->listener) {
            swoole_event_del($this->listener);
            fclose($this->listener);
            $this->listener = null;
        }
        try {
            $this->listener = Udp::listen($port, $address);
        } catch (\Throwable $throwable) {
            Trigger::throwable($throwable);
        }
    }

    private function listen()
    {
        try {
            $conf = Config::getInstance();
            $this->createListen($conf->getListenPort(), $conf->getListenAddress());
            swoole_event_add($this->listener, function ($listen) {
                $data = stream_socket_recvfrom($listen, 65533, 0, $address);
                $this->onReceive($data, $address);
            });
        } catch (\Throwable $exception) {
            Trigger::throwable($exception);
        }
    }
}