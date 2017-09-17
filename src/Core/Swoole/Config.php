<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2016/12/11
 * Time: 02:44
 */

namespace Core\Swoole;
use Conf\Config as GlobalConf;

class Config
{
    const SERVER_TYPE_SERVER = 'SERVER_TYPE_SERVER';
    const SERVER_TYPE_WEB = 'SERVER_TYPE_WEB';
    const SERVER_TYPE_WEB_SOCKET = 'SERVER_TYPE_WEB_SOCKET';

    private $listenIp;
    private $listenPort;
    private $workerSetting;
    private $workerNum;
    private $taskWorkerNum;
    private $serverName;
    private $runMode;
    private $serverType;
    private $socketType;
    private static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function __construct()
    {
        $this->listenIp = GlobalConf::getInstance()->getConf("SERVER.LISTEN");
        $this->listenPort = GlobalConf::getInstance()->getConf("SERVER.PORT");
        $this->workerSetting = GlobalConf::getInstance()->getConf("SERVER.CONFIG");
        $this->workerNum = GlobalConf::getInstance()->getConf("SERVER.CONFIG.worker_num");
        $this->taskWorkerNum = GlobalConf::getInstance()->getConf("SERVER.CONFIG.task_worker_num");
        $this->serverName = GlobalConf::getInstance()->getConf("SERVER.SERVER_NAME");
        $this->runMode = GlobalConf::getInstance()->getConf("SERVER.RUN_MODE");
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
     * @return null
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }

    /**
     * @return null
     */
    public function getWorkerSetting()
    {
        return $this->workerSetting;
    }

    /**
     * @return null
     */
    public function getWorkerNum()
    {
        return $this->workerNum;
    }

    /**
     * @return null
     */
    public function getTaskWorkerNum()
    {
        return $this->taskWorkerNum;
    }

    /**
     * @return null
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @return null
     */
    public function getRunMode()
    {
        return $this->runMode;
    }

    /**
     * @return null
     */
    public function getServerType()
    {
        return $this->serverType;
    }

    /**
     * @return null
     */
    public function getSocketType()
    {
        return $this->socketType;
    }
}