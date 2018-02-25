<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/25
 * Time: 上午11:55
 */

namespace EasySwoole\Core\Swoole\PipeMessage;


class Message
{
    private $command;
    private $data;

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
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}