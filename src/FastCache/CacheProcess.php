<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/6
 * Time: 11:10 PM
 */

namespace EasySwoole\EasySwoole\FastCache;

use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Spl\SplArray;
use Swoole\Process;

class CacheProcess extends AbstractProcess
{
    /*
     * @var $splArray SplArray
     */
    protected $splArray;
    protected $queueArray = [];
    protected $sock;

    public function run(Process $process)
    {
        \Swoole\Runtime::enableCoroutine(true);
        $this->splArray = new SplArray();
        // TODO: Implement run() method.
        go(function (){
            $index = $this->getArg('index');
            $sockfile = EASYSWOOLE_TEMP_DIR."/{$this->getProcessName()}.sock";
            $this->sock = $sockfile;
            if (file_exists($sockfile))
            {
                unlink($sockfile);
            }
            $socket = stream_socket_server("unix://$sockfile", $errno, $errstr);
            if (!$socket)
            {
                Trigger::getInstance()->error($errstr);
                return;
            }
            while (1){
                $conn = stream_socket_accept($socket,-1);
                if($conn){
                    $com = new Package();
                    stream_set_timeout($conn,2);
                    //先取4个字节的头
                    $header = fread($conn,4);
                    if(strlen($header) == 4){
                        $allLength = Protocol::packDataLength($header);
                        $data = fread($conn,$allLength );
                        if(strlen($data) == $allLength){
                            //开始数据包+命令处理，并返回数据
                            $fromPackage = unserialize($data);
                            if($fromPackage instanceof Package){
                                switch ($fromPackage->getCommand())
                                {
                                    case 'set':{
                                        $com->setValue(true);
                                        $this->splArray->set($fromPackage->getKey(),$fromPackage->getValue());
                                        break;
                                    }
                                    case 'get':{
                                        $com->setValue($this->splArray->get($fromPackage->getKey()));
                                        break;
                                    }
                                    case 'unset':{
                                        $com->setValue(true);
                                        $this->splArray->unset($fromPackage->getKey());
                                        break;
                                    }
                                    case 'keys':{
                                        $key = $fromPackage->getKey();
                                        $com->setValue($this->splArray->keys($key));
                                        break;
                                    }
                                    case 'flush':{
                                        $com->setValue(true);
                                        $this->splArray = new SplArray();
                                        break;
                                    }
                                    case 'enQueue':{
                                        $que = $this->initQueue($fromPackage->getKey());
                                        $data = $fromPackage->getValue();
                                        if($data !== null){
                                            $que->enqueue($fromPackage->getValue());
                                            $com->setValue(true);
                                        }else{
                                            $com->setValue(false);
                                        }
                                        break;
                                    }
                                    case 'deQueue':{
                                        $que = $this->initQueue($fromPackage->getKey());
                                        if($que->isEmpty()){
                                            $com->setValue(null);
                                        }else{
                                            $com->setValue($que->dequeue());
                                        }
                                        break;
                                    }
                                    case 'queueSize':{
                                        $que = $this->initQueue($fromPackage->getKey());
                                        $com->setValue($que->count());
                                        break;
                                    }
                                    case 'unsetQueue':{
                                        if(isset($this->queueArray[$fromPackage->getKey()])){
                                            unset($this->queueArray[$fromPackage->getKey()]);
                                            $com->setValue(true);
                                        }else{
                                            $com->setValue(false);
                                        }
                                        break;
                                    }
                                    case 'queueList':{
                                        $com->setValue(array_keys($this->queueArray));
                                        break;
                                    }
                                    case 'flushQueue':{
                                        $this->queueArray = [];
                                        $com->setValue(true);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    fwrite($conn,Protocol::pack(serialize($com)));
                    fclose($conn);
                }
            }
        });
    }

    private function initQueue($key):\SplQueue
    {
        if(!isset($this->queueArray[$key])){
            $this->queueArray[$key] = new \SplQueue();
        }
        return $this->queueArray[$key];
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
//        if (file_exists($this->sock))
//        {
//            unlink($this->sock);
//        }
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

}