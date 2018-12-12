<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 12:10 PM
 */

namespace EasySwoole\EasySwoole\Actor;


class ActorConfig
{
    protected $actorName;
    protected $actorClass;
    protected $actorProcessNum = 3;
    protected $maxActorNum = 10000;

    /**
     * @return mixed
     */
    public function getActorClass()
    {
        return $this->actorClass;
    }


    public function setActorClass($actorClass): ActorConfig
    {
        $this->actorClass = $actorClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getActorProcessNum(): int
    {
        return $this->actorProcessNum;
    }

    public function setActorProcessNum(int $actorProcessNum): ActorConfig
    {
        $this->actorProcessNum = $actorProcessNum;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxActorNum(): int
    {
        return $this->maxActorNum;
    }

    public function setMaxActorNum(int $maxActorNum): ActorConfig
    {
        $this->maxActorNum = $maxActorNum;
        return $this;
    }

    /**
     * @return string
     */
    public function getActorName()
    {
        return $this->actorName;
    }

    public function setActorName(string $actorName): ActorConfig
    {
        $this->actorName = $actorName;
        return $this;
    }
}