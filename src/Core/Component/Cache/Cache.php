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
            $this->processNum = $num;
            for ($i=0;$i < $num;$i++){
                $processName = "cache_process_{$i}";
                ProcessManager::getInstance()->addProcess($processName,CacheProcess::class,true);
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
        $process = ProcessManager::getInstance()->getProcessByName("cache_process_{$num}");
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('get');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        while (1){
            $msg = ProcessManager::getInstance()->readByProcessName("cache_process_{$num}",$timeOut);
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
        if(ServerManager::getInstance()->getServer()){
            $num = $this->keyToProcessNum($key);
            $msg = new Msg();
            $msg->setCommand('set');
            $msg->setArg('key',$key);
            $msg->setData($data);
            ProcessManager::getInstance()->getProcessByName("cache_process_{$num}")->getProcess()->write(\swoole_serialize::pack($msg));
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
            ProcessManager::getInstance()->getProcessByName("cache_process_{$num}")->getProcess()->write(\swoole_serialize::pack($msg));
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
                ProcessManager::getInstance()->getProcessByName("cache_process_{$i}")->getProcess()->write(\swoole_serialize::pack($msg));
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
                $process = ProcessManager::getInstance()->getProcessByName("cache_process_{$num}");
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
        $process = ProcessManager::getInstance()->getProcessByName("cache_process_{$num}");
        $msg = new  Msg();
        $msg->setArg('timeOut',$timeOut);
        $msg->setArg('key',$key);
        $msg->setCommand('deQueue');
        $msg->setToken($token);
        $process->getProcess()->write(\swoole_serialize::pack($msg));
        while (1){
            $msg = ProcessManager::getInstance()->readByProcessName("cache_process_{$num}",$timeOut);
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
        if(ServerManager::getInstance()->getServer()){
            $num = $this->keyToProcessNum($key);
            $msg = new Msg();
            $msg->setCommand('enQueue');
            $msg->setArg('key',$key);
            $msg->setData($data);
            ProcessManager::getInstance()->getProcessByName("cache_process_{$num}")->getProcess()->write(\swoole_serialize::pack($msg));
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
}