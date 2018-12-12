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
    function create($args = null,$timeout = -1)
    {
        $command = new Command();
        $command->setCommand('create');
        $command->setArg($args);
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

    function delete()
    {

    }

    function push()
    {

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
        for ($i = 0;$i < $this->conf->getActorProcessNum();$i++){
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

}