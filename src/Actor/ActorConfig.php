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
    protected $actorName = 'actor';
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

    /**
     * @param mixed $actorClass
     */
    public function setActorClass($actorClass): void
    {
        $this->actorClass = $actorClass;
    }

    /**
     * @return int
     */
    public function getActorProcessNum(): int
    {
        return $this->actorProcessNum;
    }

    /**
     * @param int $actorProcessNum
     */
    public function setActorProcessNum(int $actorProcessNum): void
    {
        $this->actorProcessNum = $actorProcessNum;
    }

    /**
     * @return int
     */
    public function getMaxActorNum(): int
    {
        return $this->maxActorNum;
    }

    /**
     * @param int $maxActorNum
     */
    public function setMaxActorNum(int $maxActorNum): void
    {
        $this->maxActorNum = $maxActorNum;
    }

    /**
     * @return string
     */
    public function getActorName(): string
    {
        return $this->actorName;
    }

    /**
     * @param string $actorName
     */
    public function setActorName(string $actorName): void
    {
        $this->actorName = $actorName;
    }
}