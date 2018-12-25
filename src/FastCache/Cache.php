<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/6
 * Time: 11:10 PM
 */

namespace EasySwoole\EasySwoole\FastCache;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;

class Cache
{
    use Singleton;

    private $processNum = 0;
    private $serverName;
    private $tickCall = null;
    private $tickInterval = 5*1000;
    private $onStart;
    private $onShutdown;

    function __construct()
    {
        $this->processNum = Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM');
        $this->serverName = Config::getInstance()->getConf('SERVER_NAME');
    }

    /**
     * @param null $tickCall
     */
    public function __setTickCall($tickCall): void
    {
        $this->tickCall = $tickCall;
    }

    /**
     * @param float|int $tickInterval
     */
    public function __setTickInterval($tickInterval): void
    {
        $this->tickInterval = $tickInterval;
    }

    /**
     * @param mixed $onStart
     */
    public function __setOnStart(callable $onStart): void
    {
        $this->onStart = $onStart;
    }

    /**
     * @param mixed $onShutdown
     */
    public function __setOnShutdown(callable $onShutdown): void
    {
        $this->onShutdown = $onShutdown;
    }

    function set($key,$value,float $timeout = 0.1)
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('set');
        $com->setValue($value);
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function get($key,float $timeout = 0.1)
    {
        if($this->processNum <= 0){
            return null;
        }
        $com = new Package();
        $com->setCommand('get');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function unset($key,float $timeout = 0.1)
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('unset');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function keys($key = null,float $timeout = 0.1):?array
    {
        if($this->processNum <= 0){
            return [];
        }
        $com = new Package();
        $com->setCommand('keys');
        $com->setKey($key);
        $data = [];
        for( $i=0 ; $i < $this->processNum ; $i++){
            $sockFile = EASYSWOOLE_TEMP_DIR."/{$this->serverName}.FastCacheProcess.{$i}.sock";
            $keys = $this->sendAndRecv('',$com,$timeout,$sockFile);
            if($keys!==null){
                $data = array_merge($data,$keys);
            }
        }
        return $data;
    }

    function flush(float $timeout = 0.1)
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('flush');
        for( $i=0 ; $i < $this->processNum ; $i++){
            $sockFile = EASYSWOOLE_TEMP_DIR."/{$this->serverName}.FastCacheProcess.{$i}.sock";
            $this->sendAndRecv('',$com,$timeout,$sockFile);
        }
        return true;
    }

    public function enQueue($key,$value,$timeout = 0.1)
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('enQueue');
        $com->setValue($value);
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function deQueue($key,$timeout = 0.1)
    {
        if($this->processNum <= 0){
            return null;
        }
        $com = new Package();
        $com->setCommand('deQueue');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function queueSize($key,$timeout = 0.1)
    {
        if($this->processNum <= 0){
            return null;
        }
        $com = new Package();
        $com->setCommand('queueSize');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function unsetQueue($key,$timeout = 0.1):?bool
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('unsetQueue');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    /*
     * 返回当前队列的全部key名称
     */
    public function queueList($timeout = 0.1):?array
    {
        if($this->processNum <= 0){
            return [];
        }
        $com = new Package();
        $com->setCommand('queueList');
        $data = [];
        for( $i=0 ; $i < $this->processNum ; $i++){
            $sockFile = EASYSWOOLE_TEMP_DIR."/{$this->serverName}.FastCacheProcess.{$i}.sock";
            $keys = $this->sendAndRecv('',$com,$timeout,$sockFile);
            if($keys!==null){
                $data = array_merge($data,$keys);
            }
        }
        return $data;
    }

    function flushQueue(float $timeout = 0.1):bool
    {
        if($this->processNum <= 0){
            return false;
        }
        $com = new Package();
        $com->setCommand('flushQueue');
        for( $i=0 ; $i < $this->processNum ; $i++){
            $sockFile = EASYSWOOLE_TEMP_DIR."/{$this->serverName}.FastCacheProcess.{$i}.sock";
            $this->sendAndRecv('',$com,$timeout,$sockFile);
        }
        return true;
    }

    private function generateSocket($key):string
    {
        //当以多维路径作为key的时候，以第一个路径为主。
        $list = explode('.',$key);
        $key = array_shift($list);
        $index = base_convert( substr(md5( $key),0,2), 16, 10 )%$this->processNum;
        return EASYSWOOLE_TEMP_DIR."/{$this->serverName}.FastCacheProcess.{$index}.sock";
    }

    private function sendAndRecv($key,Package $package,$timeout,$socketFile = null)
    {
        if(empty($socketFile)){
            $socketFile = $this->generateSocket($key);
        }
        $client = new Client($socketFile);
        $client->send(serialize($package));
        $ret =  $client->recv($timeout);
        if(!empty($ret)){
            $ret = unserialize($ret);
            if($ret instanceof Package){
                return $ret->getValue();
            }
        }
        return null;
    }

    /*
     * 请勿私自调用
     */
    function __run()
    {
        for( $i=0 ; $i < $this->processNum ; $i++){
            ServerManager::getInstance()->getSwooleServer()->addProcess(
                (new CacheProcess("{$this->serverName}.FastCacheProcess.{$i}",[
                    'index'=>$i,
                    'tickCall'=>$this->tickCall,
                    'tickInterval'=>$this->tickInterval,
                    'onStart'=>$this->onStart,
                    'onShutdown'=>$this->onShutdown
                ]))->getProcess()
            );
        }
    }
}
