<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/31
 * Time: 下午1:40
 */

namespace EasySwoole\Core\Component\Cluster\Communicate;


use EasySwoole\Core\Component\Spl\SplBean;

class CommandBean extends SplBean
{
    protected $command;
    protected $args = [];
    protected $time;

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

    protected function initialize(): void
    {
        if (empty($this->time)) {
            $this->time = time();
        }
    }
}