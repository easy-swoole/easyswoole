<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:24
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Config as GlobalConf;

class Config
{
    const TYPE_SERVER = 'TYPE_SERVER';
    const TYPE_WEB = 'TYPE_WEB';
    const TYPE_WEB_SOCKET = 'TYPE_WEB_SOCKET';

    private $listenIp;
    private $listenPort;
    private $workerSetting;
    private $workerNum;
    private $taskWorkerNum;
    private $serverType;
    private $socketType;

    use Singleton;

    function __construct()
    {
        $this->listenIp = GlobalConf::getInstance()->getConf("SERVER.LISTEN");
        $this->listenPort = GlobalConf::getInstance()->getConf("SERVER.PORT");
        $this->workerSetting = GlobalConf::getInstance()->getConf("SERVER.CONFIG");
        $this->workerNum = GlobalConf::getInstance()->getConf("SERVER.CONFIG.worker_num");
        $this->taskWorkerNum = GlobalConf::getInstance()->getConf("SERVER.CONFIG.task_worker_num");
        $this->serverType = GlobalConf::getInstance()->getConf("SERVER.SERVER_TYPE");
        $this->socketType = GlobalConf::getInstance()->getConf("SERVER.SOCKET_TYPE");
    }

    /**
     * @return null
     */
    public function getListenIp()
    {
        return $this->listenIp;
    }

    /**
     * @param null $listenIp
     */
    public function setListenIp($listenIp)
    {
        $this->listenIp = $listenIp;
    }

    /**
     * @return null
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }

    /**
     * @param null $listenPort
     */
    public function setListenPort($listenPort)
    {
        $this->listenPort = $listenPort;
    }

    /**
     * @return null
     */
    public function getWorkerSetting()
    {
        return $this->workerSetting;
    }

    /**
     * @param null $workerSetting
     */
    public function setWorkerSetting($workerSetting)
    {
        $this->workerSetting = $workerSetting;
    }

    /**
     * @return null
     */
    public function getWorkerNum()
    {
        return $this->workerNum;
    }

    /**
     * @param null $workerNum
     */
    public function setWorkerNum($workerNum)
    {
        $this->workerNum = $workerNum;
    }

    /**
     * @return null
     */
    public function getTaskWorkerNum()
    {
        return $this->taskWorkerNum;
    }

    /**
     * @param null $taskWorkerNum
     */
    public function setTaskWorkerNum($taskWorkerNum)
    {
        $this->taskWorkerNum = $taskWorkerNum;
    }

    /**
     * @return null
     */
    public function getServerType()
    {
        return $this->serverType;
    }

    /**
     * @param null $serverType
     */
    public function setServerType($serverType)
    {
        $this->serverType = $serverType;
    }

    /**
     * @return null
     */
    public function getSocketType()
    {
        return $this->socketType;
    }

    /**
     * @param null $socketType
     */
    public function setSocketType($socketType)
    {
        $this->socketType = $socketType;
    }

}