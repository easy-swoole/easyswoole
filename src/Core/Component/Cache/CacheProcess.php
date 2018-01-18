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
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Memory\ChannelManager;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;

class CacheProcess extends AbstractProcess
{
    private $cacheData = [];
    private $persistentTime = 0;
    private $lastPersistentTime = 0;
    private $inPersistent = false;
    function __construct($async,$args)
    {
        $this->persistentTime = Config::getInstance()->getConf('EASY_CACHE.PERSISTENT_TIME');
        parent::__construct(false, $args);
    }

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        if($this->persistentTime > 0){
            $processName = $this->getArgs()[0];
            Logger::getInstance()->console('loading data from file at process '.$processName);
            $this->loadData();
            Logger::getInstance()->console('loading data from file success at process '.$processName);
        }
        //每1000us执行一次调度
        $this->setTick(function (){
            $processName = $this->getArgs()[0];
            $channel = ChannelManager::getInstance()->get($processName);
            while (1){
                $data = $channel->pop();
                if(empty($data)){
                    break;
                }
                if(isset($data['command'])){
                    $command = $data['command'];
                    switch ($command){
                        case 'set':{
                            $key = $data['args']['key'];
                            $data = $data['args']['data'];
                            $this->cacheData[$key] = $data;
                            break;
                        }
                        case 'get':{
                            $key = $data['args']['key'];
                            $token = $data['args']['token'];
                            $ret = null;
                            if(isset($this->cacheData[$key])){
                                $ret = $this->cacheData[$key];
                            }
                            TableManager::getInstance()->get('process_cache_buff')->set($token,[
                                'data'=>\swoole_serialize::pack($ret),
                                'time'=>time()
                            ]);
                            break;
                        }
                        case 'del':{
                            $key = $data['args']['key'];
                            if(isset($this->cacheData[$key])){
                                unset($this->cacheData[$key]);
                            }
                            break;
                        }
                        case 'flush':{
                            $this->flush();
                            break;
                        }
                    }
                }
            }
            if($this->persistentTime > 0 && !$this->inPersistent){
                if(time() - $this->lastPersistentTime > $this->persistentTime){
                    $this->inPersistent = true;
                    $this->lastPersistentTime = time();
                    $this->saveData();
                    $this->inPersistent = false;
                }
            }
        },1000);
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
       if($this->lastPersistentTime > 0){
           $this->saveData();
       }
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

    private function saveData()
    {
        $processName = $this->getArgs()[0];
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/{$processName}.data";
        file_put_contents($file,\swoole_serialize::pack($this->cacheData),LOCK_EX);
    }

    private function loadData()
    {
        $processName = $this->getArgs()[0];
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/{$processName}.data";
        if(file_exists($file)){
            $this->cacheData = \swoole_serialize::unpack(file_get_contents($file));
        }

    }

    private function flush()
    {
        $this->cacheData = [];
        $this->saveData();
    }
}