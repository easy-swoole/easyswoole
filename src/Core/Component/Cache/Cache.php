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
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Random;

class Cache
{
    use Singleton;
    private $processNum;
    private $processList = [];
    private $cliTemp = null;//支持单元测试和服务启动前的临时数据存储

    function __construct()
    {
        $num = intval(Config::getInstance()->getConf("EASY_CACHE.PROCESS_NUM"));
        if($num <= 0){
           return;
        }
        $this->cliTemp = new SplArray();
        $this->processNum = $num;
        for ($i=0;$i < $num;$i++){
            $processName = "process_cache_{$i}";
            $hash = ProcessManager::getInstance()->addProcess(CacheProcess::class,false,$processName);
            $this->processList[$processName] = ProcessManager::getInstance()->getProcessByHash($hash);
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
        $process = $this->processList["process_cache_{$num}"];
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('get');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        while (1){
            $msg = ProcessManager::getInstance()->readByHash($process->getHash(),$timeOut);
            if(!empty($msg)){
                $msg = \swoole_serialize::unpack($msg);
                if($msg instanceof Msg){
                    if($msg->getToken() == $token){
                        return $msg->getData();
                    }else{
                        //参与重新调度
                        if($msg->getToken()){
                            $msg->setCommand('reDispatch');
                            $process->getProcess()->write(\swoole_serialize::pack($msg));
                        }
                    }
                }else{
                    return null;
                }
            }else{
                return null;
            }

        }
    }

    public function set($key,$data)
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->set($key,$data);
        }
        $num = $this->keyToProcessNum($key);
        $msg = new Msg();
        $msg->setCommand('set');
        $msg->setArg('key',$key);
        $msg->setData($data);
        $this->processList["process_cache_{$num}"]->getProcess()->write(\swoole_serialize::pack($msg));
    }

    function del($key)
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->delete($key);
        }
        $num = $this->keyToProcessNum($key);
        $msg = new Msg();
        $msg->setCommand('del');
        $msg->setArg('key',$key);
        $this->processList["process_cache_{$num}"]->getProcess()->write(\swoole_serialize::pack($msg));
    }

    function flush()
    {
        if(!ServerManager::getInstance()->isStart()){
            $this->cliTemp->flush();
        }
        $msg = new Msg();
        $msg->setCommand('flush');
        for ($i=0;$i<$this->processNum;$i++){
            $this->processList["process_cache_{i}"]->getProcess()->write(\swoole_serialize::pack($msg));
        }
    }

    public function deQueue($key,$timeOut = 0.01)
    {
        if(!ServerManager::getInstance()->isStart()){
            //依旧发送队列,但不发token
            $num = $this->keyToProcessNum($key);
            $process = $this->processList["process_cache_{$num}"];
            $msg = new  Msg();
            $msg->setArg('timeOut',$timeOut);
            $msg->setArg('key',$key);
            $msg->setCommand('deQueue');
            $process->getProcess()->write(\swoole_serialize::pack($msg));
            $que = $this->cliTemp->get($key);
            if(!$que instanceof \SplQueue){
                $que = new \SplQueue();
                $this->cliTemp->set($key,$que);
            }
            $ret = null;
            if(!$que->isEmpty()){
                $ret = $que->dequeue();
            }
            return $ret;
        }
        $num = $this->keyToProcessNum($key);
        $token = Random::randStr(9);
        $process = $this->processList["process_cache_{$num}"];
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('deQueue');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        while (1){
            $msg = ProcessManager::getInstance()->readByHash($process->getHash(),$timeOut);
            if(!empty($msg)){
                $msg = \swoole_serialize::unpack($msg);
                if($msg instanceof Msg){
                    if($msg->getToken() == $token){
                        return $msg->getData();
                    }else{
                        //参与重新调度
                        if($msg->getToken()){
                            $msg->setCommand('reDispatch');
                            $process->getProcess()->write(\swoole_serialize::pack($msg));
                        }
                    }
                }else{
                    return null;
                }
            }else{
                return null;
            }
        }
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
        $num = $this->keyToProcessNum($key);
        $msg = new Msg();
        $msg->setCommand('enQueue');
        $msg->setArg('key',$key);
        $msg->setData($data);
        $this->processList["process_cache_{$num}"]->getProcess()->write(\swoole_serialize::pack($msg));
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
}