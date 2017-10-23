<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午1:20
 */

namespace Core\Component\RPC\Common;


use Core\Component\Spl\SplBean;

class Config extends SplBean
{
    protected $host;
    protected $port;
    protected $eof = '0x0d0x0a';
    protected $heartBeatCheckInterval  = 30;
    protected $packageParserClass = DefaultPackageParser::class;
    protected $connectTimeOut = 0.5;

    /**
     * @return float
     */
    public function getConnectTimeOut()
    {
        return $this->connectTimeOut;
    }

    /**
     * @param float $connectTimeOut
     */
    public function setConnectTimeOut($connectTimeOut)
    {
        $this->connectTimeOut = $connectTimeOut;
    }


    /**
     * @return string
     */
    public function getEof()
    {
        return $this->eof;
    }

    /**
     * @param string $eof
     */
    public function setEof($eof)
    {
        $this->eof = $eof;
    }

    /**
     * @return int
     */
    public function getHeartBeatCheckInterval()
    {
        return $this->heartBeatCheckInterval;
    }

    /**
     * @param int $heartBeatCheckInterval
     */
    public function setHeartBeatCheckInterval($heartBeatCheckInterval)
    {
        $this->heartBeatCheckInterval = $heartBeatCheckInterval;
    }

    /**
     * @return mixed
     */
    public function getPackageParserClass()
    {
        return $this->packageParserClass;
    }

    /**
     * @param mixed $packageParserClass
     */
    public function setPackageParserClass($packageParserClass)
    {
        $this->packageParserClass = $packageParserClass;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

}