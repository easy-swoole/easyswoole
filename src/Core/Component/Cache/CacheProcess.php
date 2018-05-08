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
    function __construct(string $processName, array $args)
    {
        $this->cacheData = new SplArray();
        $this->persistentTime = Config::getInstance()->getConf('EASY_CACHE.PERSISTENT_TIME');
        parent::__construct($processName, $args);
    }

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        if($this->persistentTime > 0){
            $processName = $this->getProcessName();
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
        $table = TableManager::getInstance()->get(Cache::EXCHANGE_TABLE_NAME);
        if(count($table) > 1900){
            //接近阈值的时候进行gc检测
            //遍历Table 依赖pcre 如果发现无法遍历table,检查机器是否安装pcre-devel
            //超过0.1s 基本上99.99%为无用数据。
            $time = microtime(true);
            foreach ($table as $key => $item){
                if(round($time - $item['microTime']) > 0.1){
                    $table->del($key);
                }
            }
        }
        if($msg instanceof Msg){
            switch ($msg->getCommand()){
                case 'set':{
                    $this->cacheData->set($msg->getArg('key'),$msg->getData());
                    break;
                }
                case 'get':{
                    $ret = $this->cacheData->get($msg->getArg('key'));
                    $msg->setData($ret);
                    $table->set($msg->getToken(),[
                        'data'=>\swoole_serialize::pack($msg),
                        'microTime'=>microtime(true)
                    ]);
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
                    //deQueue 有cli 服务未启动的请求，但无token
                    if(!empty($msg->getToken())){
                        $table->set($msg->getToken(),[
                            'data'=>\swoole_serialize::pack($msg),
                            'microTime'=>microtime(true)
                        ]);
                    }
                    break;
                }
                case 'queueSize':{
                    $que = $this->cacheData->get($msg->getArg('key'));
                    if(!$que instanceof \SplQueue){
                        $que = new \SplQueue();
                    }
                    $msg->setData($que->count());
                    $table->set($msg->getToken(),[
                        'data'=>\swoole_serialize::pack($msg),
                        'microTime'=>microtime(true)
                    ]);
                    break;
                }
            }
        }
    }

    private function saveData()
    {
        $processName = $this->getProcessName();
        $file = Config::getInstance()->getConf('TEMP_DIR')."/{$processName}.data";
        file_put_contents($file,\swoole_serialize::pack($this->cacheData->getArrayCopy()),LOCK_EX);
    }

    private function loadData()
    {
        $processName = $this->getProcessName();
        $file = Config::getInstance()->getConf('TEMP_DIR')."/{$processName}.data";
        if(file_exists($file)){
            $data = \swoole_serialize::unpack(file_get_contents($file));
            if(!is_array($data)){
                $data = [];
            }
            $this->cacheData->loadArray($data);
        }
    }
}