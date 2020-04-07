<?php


namespace EasySwoole\EasySwoole\Bridge;


class Package
{
    protected $status = 1;
    protected $command;
    protected $args;

    const STATUS_PACKAGE_ERROR = -1;
    const STATUS_COMMAND_ERROR = -2;
    const STATUS_SUCCESS = 1;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args): void
    {
        $this->args = $args;
    }


}
