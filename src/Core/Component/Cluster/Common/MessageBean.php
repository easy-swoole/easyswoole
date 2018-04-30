<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:09
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\Component\Spl\SplBean;

class MessageBean extends SplBean
{
    protected $time;
    protected $command;
    protected $args = [];
    protected $fromNode;

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time): void
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getFromNode():?NodeBean
    {
        return $this->fromNode;
    }

    /**
     * @param mixed $fromNode
     */
    public function setFromNode($fromNode): void
    {
        $this->fromNode = $fromNode;
    }

    protected function initialize(): void
    {
       if(empty($this->time)){
           $this->time = time();
       }
       if(is_array($this->fromNode)){
           $this->fromNode = new NodeBean($this->fromNode);
       }else{
           $this->fromNode = null;
       }
    }
}