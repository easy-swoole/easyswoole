<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 上午2:25
 */

namespace Core\Swoole\Pipe;


use Core\Component\Spl\SplBean;

class Message extends SplBean
{
    protected $command;
    protected $message;

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
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }



    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

}