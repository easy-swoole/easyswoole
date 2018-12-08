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

    function __construct()
    {
        $this->processNum = Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM');
    }

    function set($key,$value,float $timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('set');
        $com->setValue($value);
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function get($key,float $timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('get');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function unset($key,float $timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('unset');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    function flush()
    {

    }

    public function enQueue($key,$value,$timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('enQueue');
        $com->setValue($value);
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function deQueue($key,$timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('deQueue');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function queueSize($key,$timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('queueSize');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    public function unsetQueue($key,$timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('unsetQueue');
        $com->setKey($key);
        return $this->sendAndRecv($key,$com,$timeout);
    }

    /*
     * 返回当前队列的全部key名称
     */
    public function queueList($timeout = 0.1)
    {
        $com = new Package();
        $com->setCommand('queueList');
        return $this->sendAndRecv('',$com,$timeout);
    }

    private function generateSocket($key):string
    {
        //当以多维路径作为key的时候，以第一个路径为主。
        $list = explode('.',$key);
        $key = array_shift($list);
        $index = base_convert( md5( $key,true ), 16, 10 )%$this->processNum;
        return EASYSWOOLE_TEMP_DIR."/server{$index}.sock";
    }

    private function sendAndRecv($key,Package $package,$timeout)
    {
        $client = new Client($this->generateSocket($key));
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
    function __hook()
    {
        $this->processNum = Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM');
        $this->serverName = Config::getInstance()->getConf('SERVER_NAME');
        for( $i=0 ; $i < $this->processNum ; $i++){
            ServerManager::getInstance()->getSwooleServer()->addProcess((new CacheProcess("{$this->serverName}.FAST_CACHE",['index'=>$i]))->getProcess());
        }
    }
}