<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 11:28 AM
 */

namespace EasySwoole\EasySwoole\Actor;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;

class ActorManager
{
    use Singleton;

    protected $list = [];

    function register(string $actorClass):ActorConfig
    {
        $ref = new \ReflectionClass($actorClass);
        if($ref->isSubclassOf(AbstractActor::class)){
            $conf = new ActorConfig();
            $conf->setActorClass($actorClass);
            $this->list[$actorClass] = $conf;
            return $conf;
        }else{
            throw new \Exception("{$actorClass} not a sub class of ".AbstractActor::class);
        }
    }

    function actorClient(string $actorClass):?ActorClient
    {
        if(isset($this->list[$actorClass])){
            return new ActorClient($this->list[$actorClass]);
        }else{
            return null;
        }
    }

    function __run()
    {
        $name = Config::getInstance()->getConf('SERVER_NAME');
        foreach ($this->list as $conf){
            $num = $conf->getActorProcessNum();
            $subName = "{$name}.ActorProcess.{$conf->getActorName()}";
            for ($index = 0;$index < $num; $index++){
                $process = new ActorProcess("{$subName}.{$index}",[
                    'index'=>$index,
                    'actorClass'=>$conf->getActorClass()
                ]);
                ServerManager::getInstance()->getSwooleServer()->addProcess($process->getProcess());
            }
        }
    }
}