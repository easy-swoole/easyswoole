<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午6:14
 */

namespace Core\Component\Socket\Common;


class Command
{
    protected $command;
    protected $message;
    protected $client;
    protected $rawData;

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

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    function setClient($client){
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @param mixed $rawData
     */
    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
    }
}