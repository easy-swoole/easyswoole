<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/18
 * Time: 下午12:17
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Random;
use Swoole\Table;

class Cache
{
    use Singleton;
    const EXCHANGE_TABLE_NAME = '__Cache';
    private $processNum;
    private $cliTemp = null;//支持单元测试和服务启动前的临时数据存储

    function __construct()
    {
        $num = intval(Config::getInstance()->getConf("EASY_CACHE.PROCESS_NUM"));
        if($num <= 0){
           return;
        }
        $this->cliTemp = new SplArray();
        //若是在主服务创建，而非单元测试调用
        if(ServerManager::getInstance()->getServer()){
            //创建table用于数据传递
            TableManager::getInstance()->add(self::EXCHANGE_TABLE_NAME,[
                'data'=>[
                    'type'=>Table::TYPE_STRING,
                    'size'=>10*1024
                ],
                'microTime'=>[
                    'type'=>Table::TYPE_STRING,
                    'size'=>15
                ]
            ],2048);
            $this->processNum = $num;
            for ($i=0;$i < $num;$i++){
                ProcessManager::getInstance()->addProcess($this->generateProcessName($i),CacheProcess::class);
            }
        }
    }

    /*
     * 默认等待0.01秒的调度
     */
    public function get($key,$timeOut = 0.01)
    {
        if(!ServerManager::getInstance()->isStart()){
            return $this->cliTemp->get($key);
        }
        $num = $this->keyToProcessNum($key);
        $token = Random::randStr(9);
        $process = ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num));
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('get');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        return $this->read($token,$timeOut);
    }

    public function set($key,$data)
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->set($key,$data);
        }
        if(ServerManager::getInstance()->getServer()){
            $num = $this->keyToProcessNum($key);
            $msg = new Msg();
            $msg->setCommand('set');
            $msg->setArg('key',$key);
            $msg->setData($data);
            ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num))->getProcess()->write(\swoole_serialize::pack($msg));
        }
    }

    function del($key)
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->delete($key);
        }
        if(ServerManager::getInstance()->getServer()){
            $num = $this->keyToProcessNum($key);
            $msg = new Msg();
            $msg->setCommand('del');
            $msg->setArg('key',$key);
            ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num))->getProcess()->write(\swoole_serialize::pack($msg));
        }
    }

    function flush()
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->flush();
        }
        if(ServerManager::getInstance()->getServer()){
            $msg = new Msg();
            $msg->setCommand('flush');
            for ($i=0;$i<$this->processNum;$i++){
                ProcessManager::getInstance()->getProcessByName($this->generateProcessName($i))->getProcess()->write(\swoole_serialize::pack($msg));
            }
        }
    }

    public function deQueue($key,$timeOut = 0.01)
    {
        if(!ServerManager::getInstance()->isStart()){
            $que = $this->cliTemp->get($key);
            if(!$que instanceof \SplQueue){
                $que = new \SplQueue();
                $this->cliTemp->set($key,$que);
            }
            $ret = null;
            if(!$que->isEmpty()){
                $ret = $que->dequeue();
            }
            if(ServerManager::getInstance()->getServer()){
                //依旧发送队列,但不发token
                $num = $this->keyToProcessNum($key);
                $process = ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num));
                $msg = new  Msg();
                $msg->setArg('timeOut',$timeOut);
                $msg->setArg('key',$key);
                $msg->setCommand('deQueue');
                $process->getProcess()->write(\swoole_serialize::pack($msg));
            }
            return $ret;
        }
        $num = $this->keyToProcessNum($key);
        $token = Random::randStr(9);
        $process = ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num));
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('deQueue');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        return $this->read($token,$timeOut);
    }

    public function enQueue($key,$data)
    {
        if(!ServerManager::getInstance()->isStart()){
            $que = $this->cliTemp->get($key);
            if(!$que instanceof \SplQueue){
                $que = new \SplQueue();
                $this->cliTemp->set($key,$que);
            }
            $que->enqueue($data);
        }
        if(ServerManager::getInstance()->getServer()){
            $num = $this->keyToProcessNum($key);
            $msg = new Msg();
            $msg->setCommand('enQueue');
            $msg->setArg('key',$key);
            $msg->setData($data);
            ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num))->getProcess()->write(\swoole_serialize::pack($msg));
        }
    }

    function queueSize($key,$timeOut = 0.01):int
    {
        if(!ServerManager::getInstance()->isStart()){
            $que = $this->cliTemp->get($key);
            if($que instanceof \SplQueue){
                return $que->count();
            }
            return 0;
        }else{
            $num = $this->keyToProcessNum($key);
            $token = Random::randStr(9);
            $process = ProcessManager::getInstance()->getProcessByName($this->generateProcessName($num));
            $msg = new  Msg();
            $msg->setArg('timeOut',$timeOut);
            $msg->setArg('key',$key);
            $msg->setCommand('queueSize');
            $msg->setToken($token);
            $process->getProcess()->write(\swoole_serialize::pack($msg));
            return intval($this->read($token,$timeOut));
        }
    }

    public function clearQueue($key)
    {
        $this->del($key);
    }

    private function keyToProcessNum($key):int
    {
        //当以多维路径作为key的时候，以第一个路径为主。
        $list = explode('.',$key);
        $key = array_shift($list);
        return base_convert( md5( $key,true ), 16, 10 )%$this->processNum;
    }

    private function read($token,$timeOut)
    {
        $table = TableManager::getInstance()->get(self::EXCHANGE_TABLE_NAME);
        $start = microtime(true);
        $data = null;
        while(true){
            usleep(1);
            if($table->exist($token)){
                $data = $table->get($token)['data'];
                $data = \swoole_serialize::unpack($data);
                if(!$data instanceof Msg){
                    $data = null;
                }
                break;
            }
            if(round($start - microtime(true),3) > $timeOut){
                break;
            }
        }
        $table->del($token);
        if($data){
            return $data->getData();
        }else{
            return null;
        }
    }


    function reBootProcess()
    {
        $num = $this->processNum;
        for ($i=0;$i < $num;$i++){
            ProcessManager::getInstance()->reboot($this->generateProcessName($i));
        }
    }

    private function generateProcessName(int $processId):string
    {
        $name = Config::getInstance()->getConf('SERVER_NAME');
        return "{$name}_Cache_Process_{$processId}";
    }
}