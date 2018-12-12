<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 11:27 AM
 */

namespace EasySwoole\EasySwoole\Actor;

use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine\Channel;
use Swoole\Process;

class ActorProcess extends AbstractProcess
{
    protected $actorIndex = 1;
    protected $actorAtomic = 0;
    protected $processIndex;
    protected $actorList = [];
    protected $actorClass;
    public function run(Process $process)
    {
        // TODO: Implement run() method.
        \Swoole\Runtime::enableCoroutine(true);
        // TODO: Implement run() method.
        go(function (){
            $index = $this->getArg('index');
            $this->actorClass = $this->getArg('actorClass');
            $this->processIndex = str_pad($index,3,'0',STR_PAD_LEFT);
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
                    stream_set_timeout($conn,2);
                    //先取4个字节的头
                    $header = fread($conn,4);
                    if(strlen($header) == 4){
                        $allLength = Protocol::packDataLength($header);
                        $data = fread($conn,$allLength );
                        if(strlen($data) == $allLength){
                            //开始数据包+命令处理，并返回数据
                            $fromPackage = unserialize($data);
                            if($fromPackage instanceof Command){
                                switch ($fromPackage->getCommand()){
                                    case 'create':{
                                        $actorId = $this->processIndex.$this->actorIndex;
                                        $this->actorIndex++;
                                        $this->actorAtomic++;
                                        try{
                                            go(function ()use($actorId,$fromPackage){
                                                $actor = new $this->actorClass($actorId,new Channel(8),$fromPackage->getArg());
                                                $this->actorList[$actorId] = $actor;
                                                $actor->__run();
                                            });
                                        }catch (\Throwable $throwable){
                                            $this->actorAtomic--;
                                            Trigger::getInstance()->throwable($throwable);
                                            $actorId = null;
                                        }
                                        fwrite($conn,Protocol::pack(serialize($actorId)));
                                        fclose($conn);
                                        break;
                                    }
                                    case 'sendTo':{
                                        $args = $fromPackage->getArg();
                                        if(isset($args['actorId'])){
                                            $actorId = $args['actorId'];
                                            if(isset($this->actorList[$actorId])){
                                                //消息回复在actor中
                                                $this->actorList[$actorId]->getChannel()->push([
                                                    'connection'=>$conn,
                                                    'msg'=>$args['msg']
                                                ]);
                                                if($args['msg'] == 'exit'){
                                                    $this->actorAtomic--;
                                                    unset($this->actorList[$actorId]);
                                                }
                                                break;
                                            }
                                        }
                                        fwrite($conn,Protocol::pack(serialize(null)));
                                        fclose($conn);
                                        break;
                                    }
                                    case 'createdNum':{
                                        fwrite($conn,Protocol::pack(serialize($this->actorAtomic)));
                                        fclose($conn);
                                        break;
                                    }
                                    default:{
                                        fwrite($conn,Protocol::pack(serialize(null)));
                                        fclose($conn);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }
}