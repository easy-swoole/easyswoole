<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/18
 * Time: 下午12:18
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Config;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Memory\ChannelManager;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;

class CacheProcess extends AbstractProcess
{
    private $cacheData = null;
    private $persistentTime = 0;
    function __construct(string $processName, bool $async = true, array $args)
    {
        $this->cacheData = new SplArray();
        $this->persistentTime = Config::getInstance()->getConf('EASY_CACHE.PERSISTENT_TIME');
        parent::__construct($processName, $async, $args);
    }

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        if($this->persistentTime > 0){
            $processName = $this->getArgs()[0];
            Logger::getInstance()->console('loading data from file at process '.$processName);
            $this->loadData();
            Logger::getInstance()->console('loading data from file success at process '.$processName);
            $this->addTick($this->persistentTime*1000,function (){
                $this->saveData();
            });
        }
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
       if($this->persistentTime > 0){
           $this->saveData();
       }
    }

    public function onReceive(string $str,...$agrs)
    {
        // TODO: Implement onReceive() method.
        $msg = \swoole_serialize::unpack($str);
        if($msg instanceof Msg){
            switch ($msg->getCommand()){
                case 'set':{
                    $this->cacheData->set($msg->getArg('key'),$msg->getData());
                    break;
                }
                case 'get':{
                    $ret = $this->cacheData->get($msg->getArg('key'));
                    $msg->setData($ret);
                    $this->getProcess()->write(\swoole_serialize::pack($msg));
                    break;
                }
                case 'del':{
                    $this->cacheData->delete($msg->getArg('key'));
                    break;
                }
                case 'flush':{
                    $this->cacheData->flush();
                    break;
                }
                case 'enQueue':{
                    $que = $this->cacheData->get($msg->getArg('key'));
                    if(!$que instanceof \SplQueue){
                        $que = new \SplQueue();
                        $this->cacheData->set($msg->getArg('key'),$que);
                    }
                    $que->enqueue($msg->getData());
                    break;
                }
                case 'deQueue':{
                    $que = $this->cacheData->get($msg->getArg('key'));
                    if(!$que instanceof \SplQueue){
                        $que = new \SplQueue();
                        $this->cacheData->set($msg->getArg('key'),$que);
                    }
                    $ret = null;
                    if(!$que->isEmpty()){
                        $ret = $que->dequeue();
                    }
                    $msg->setData($ret);
                    $this->getProcess()->write(\swoole_serialize::pack($msg));
                    break;
                }
                case 'reDispatch':{
                    $msgTime = $msg->getTime();
                    $time = microtime(true);
                    if(round($time - $msgTime,4) < $msg->getArg('timeOut')){
                        $this->getProcess()->write($str);
                    }
                    break;
                }
            }
        }
    }

    private function saveData()
    {
        $processName = $this->getArgs()[0];
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/{$processName}.data";
        file_put_contents($file,\swoole_serialize::pack($this->cacheData->getArrayCopy()),LOCK_EX);
    }

    private function loadData()
    {
        $processName = $this->getArgs()[0];
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/{$processName}.data";
        if(file_exists($file)){
            $data = \swoole_serialize::unpack(file_get_contents($file));
            if(!is_array($data)){
                $data = [];
            }
            $this->cacheData->loadArray($data);
        }
    }
}