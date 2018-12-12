<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 3:12 PM
 */

namespace EasySwoole\EasySwoole\Actor;


use EasySwoole\EasySwoole\Config;
use Swoole\Coroutine\Channel;

class ActorClient
{
    protected $conf;

    function __construct(ActorConfig $conf)
    {
        $this->conf = $conf;
    }

    /*
     * 创建默认一直等待
     */
    function create($arg = null,$timeout = -1)
    {
        $command = new Command();
        $command->setCommand('create');
        $command->setArg($arg);
        //快速获得全部进程的创建结果
        $info = $this->status();
        //先计算总数 并找出最小key
        $minKey = null;
        $minNum = null;
        $all = 0;
        foreach ($info['actorNumInfo'] as $index => $createdNum)
        {
            $all = $all + $createdNum;
            if($createdNum <= $minNum){
                $minKey = $index;
                $minNum = $createdNum;
            }else if($minNum === null){
                $minNum = $createdNum;
                $minKey = $index;
            }
        }
        if($all > $this->conf->getMaxActorNum()){
            return -1;
        }else{
            return $this->sendAndRecv($command,$timeout,$this->generateSocket($minKey));
        }
    }

    function exit(string $actorId,$timeout = 0.1)
    {
        return $this->push($actorId,'exit',$timeout);
    }

    function exitAll($timeout = 0.1)
    {
        $command = new Command();
        $command->setCommand('exitAll');
        return $this->broadcast($command,$timeout);
    }

    function push(string $actorId,$arg = null,$timeout = 0.1)
    {
        $processIndex = self::actorIdToProcessIndex($actorId);
        $command = new Command();
        $command->setCommand('sendTo');
        $command->setArg([
            'actorId'=>$actorId,
            'msg'=>$arg
        ]);
        return $this->sendAndRecv($command,$timeout,$this->generateSocket($processIndex));
    }

    /*
     * ['actorId1'=>$data,'actorId2'=>$data]
     */
    function pushMulti(array $data,$timeout = 0.1)
    {
        $allNum = count($data);
        $channel = new Channel($allNum+1);
        foreach ($data as $actorId => $msg){
            go(function ()use($channel,$actorId,$msg,$timeout){
                $channel->push([
                    $actorId=>$this->push($actorId,$msg,$timeout)
                ]);
            });
        }
        $ret = [];
        $start = microtime(true);
        while (1){
            if(microtime(true) - $start > $timeout){
                break;
            }
            $temp = $channel->pop($timeout);
            if(is_array($temp)){
                $ret = $ret + $temp;
            }
        }
        return $ret;
    }

    function broadcastPush($arg,$timeout = 0.1)
    {
        $command = new Command();
        $command->setCommand('broadcast');
        $command->setArg($arg);
        return $this->broadcast($command,$timeout);
    }

    function status($timeout = 0.1)
    {
        $command = new Command();
        $command->setCommand('createdNum');
        return [
            'actorClass'=>$this->conf->getActorClass(),
            'actorName'=>$this->conf->getActorName(),
            'actorMaxNum'=>$this->conf->getMaxActorNum(),
            'actorProcessNum'=>$this->conf->getActorProcessNum(),
            'actorNumInfo'=> $this->broadcast($command,$timeout)
        ];
    }

    private function broadcast(Command $command,$timeout = 0.1)
    {
        $info = [];
        $channel = new Channel($this->conf->getActorProcessNum()+1);
        for ($i = 0;$i < $this->conf->getActorProcessNum();$i++){
            go(function ()use($command,$channel,$i,$timeout){
                $ret = $this->sendAndRecv($command,$timeout,$this->generateSocket($i));
                $channel->push([
                    'index'=>$i,
                    'result'=>$ret
                ]);
            });
        }
        $start = microtime(true);
        while (1){
            if(microtime(true) - $start > $timeout){
                break;
            }
            $temp = $channel->pop($timeout);
            if(is_array($temp)){
                $info[$temp['index']] = $temp['result'];
            }
        }
        return $info;
    }

    private function generateSocket($index):string
    {
        $name = Config::getInstance()->getConf('SERVER_NAME');
        return EASYSWOOLE_TEMP_DIR."/{$name}.ActorProcess.{$this->conf->getActorName()}.{$index}.sock";
    }

    private function sendAndRecv(Command $command,$timeout,$socketFile)
    {
        $client = new Client($socketFile);
        $client->send(serialize($command));
        $ret =  $client->recv($timeout);
        if(!empty($ret)){
            return unserialize($ret);
        }
        return null;
    }

    public static function actorIdToProcessIndex(string $actorId):int
    {
        $processIndex = ltrim(substr($actorId,0,3),'0');
        if(empty($processIndex)){
            return 0;
        }else{
            return $processIndex;
        }
    }

}