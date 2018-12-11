<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 11:28 AM
 */

namespace EasySwoole\EasySwoole\Actor;


use EasySwoole\Component\Singleton;

class ActorManager
{
    use Singleton;

    protected $list = [];

    function register(string $actorClass)
    {
        $ref = new \ReflectionClass($actorClass);
        if($ref->isSubclassOf(AbstractActor::class)){
            $conf = new ActorConfig();
            $conf->setActorClass($actorClass);
        }else{
            throw new \Exception("{$actorClass} not a sub class of ".AbstractActor::class);
        }
    }

    function __run()
    {

    }
}