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
    function __construct($async,$args)
    {
        $this->cacheData = new SplArray();
        $this->persistentTime = Config::getInstance()->getConf('EASY_CACHE.PERSISTENT_TIME');
        parent::__construct(true, $args);
    }

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        if($this->persistentTime > 0){
            $processName = $this->getArgs()[0];
            Logger::getInstance()->console('loading data from file at process '.$processName);
            $this->loadData();
            Logger::getInstance()->console('loading data from file success at process '.$processName);
            $this->setTick(function (){
                $this->saveData();
            },$this->persistentTime*1000*1000);
        }
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
       if($this->persistentTime > 0){
           $this->saveData();
       }
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
        $data = \swoole_serialize::unpack($str);
        if(isset($data['command'])){
            $command = $data['command'];
            switch ($command){
                case 'set':{
                    $key = $data['args']['key'];
                    if(isset($data['args']['tempFile'])){
                        //还原数据以节约内存
                        $data = \swoole_serialize::unpack(Utility::readFile($data['args']['tempFile']));
                    }else{
                        $data = $data['args']['data'];
                    }
                    $this->cacheData->set($key,$data);
                    break;
                }
                case 'get':{
                    $key = $data['args']['key'];
                    $ret = $this->cacheData->get($key);
                    $is = Utility::isOutOfLength($ret);
                    if($is){
                        $this->getProcess()->write(\swoole_serialize::pack(
                            [
                                'tempFile'=>Utility::writeFile($is),
                                'time'=>microtime(true),
                                'token'=> $data['args']['token'],
                                'timeOut'=>$data['timeOut']
                            ]
                        ));
                    }else{
                        $this->getProcess()->write(\swoole_serialize::pack(
                            [
                                'data'=>$ret,
                                'time'=>microtime(true),
                                'token'=> $data['args']['token'],
                                'timeOut'=>$data['timeOut']
                            ]
                        ));
                    }
                    break;
                }
                case 'del':{
                    $key = $data['args']['key'];
                    $this->cacheData->delete($key);
                    break;
                }
                case 'flush':{
                    $this->flush();
                    break;
                }
                case 'enQueue':{
                    $key = $data['args']['key'];
                    if(isset($data['args']['tempFile'])){
                        //还原数据以节约内存
                        $data = \swoole_serialize::unpack(Utility::readFile($data['args']['tempFile']));
                    }else{
                        $data = $data['args']['data'];
                    }
                    $que = $this->cacheData->get($key);
                    if(!$que instanceof \SplQueue){
                        $que = new \SplQueue();
                        $this->cacheData->set($key,$que);
                    }
                    $que->enqueue($data);
                    break;
                }
                case 'deQueue':{
                    $key = $data['args']['key'];
                    $ret = $this->cacheData->get($key);
                    if($ret instanceof \SplQueue){
                        if(!$ret->isEmpty()){
                            $ret = $ret->dequeue();
                        }else{
                            $ret = null;
                        }
                    }else{
                        $ret = null;
                    }
                    $is = Utility::isOutOfLength($ret);
                    if($is){
                        $this->getProcess()->write(\swoole_serialize::pack(
                            [
                                'tempFile'=>Utility::writeFile($is),
                                'time'=>microtime(true),
                                'token'=> $data['args']['token'],
                                'timeOut'=>$data['timeOut']
                            ]
                        ));
                    }else{
                        $this->getProcess()->write(\swoole_serialize::pack(
                            [
                                'data'=>$ret,
                                'time'=>microtime(true),
                                'token'=> $data['args']['token'],
                                'timeOut'=>$data['timeOut']
                            ]
                        ));
                    }
                    break;
                }
                case 'reDispatch':{
                    $msgTime = $data['time'];
                    $time = microtime(true);
                    if(round($time - $msgTime,4) < $data['timeOut']){
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

    private function flush()
    {
        $this->cacheData->flush();
        $this->saveData();
    }
}