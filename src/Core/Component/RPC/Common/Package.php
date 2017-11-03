<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午2:34
 */

namespace Core\Component\RPC\Common;


use Core\Component\Spl\SplBean;

class Package extends SplBean
{
    const ERROR_SERVER_NOT_FOUND = -1;
    const ERROR_ACTION_NOT_FOUND = -2;
    const ERROR_SERVER_ERROR = -3;
    const ERROR_SERVER_CONNECT_FAIL = -4;
    const ERROR_SERVER_RESPONSE_TIME_OUT = -5;
    protected $serverName;
    protected $action;
    protected $args;
    protected $message;
    protected $errorCode;
    protected $errorMsg;

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @param mixed $errorMsg
     */
    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }



    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }




    /**
     * @return mixed
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param mixed $serverName
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
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
    public function setArgs($args)
    {
        $this->args = $args;
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