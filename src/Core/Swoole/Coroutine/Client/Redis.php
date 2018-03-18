<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/18
 * Time: 下午11:45
 */

namespace EasySwoole\Core\Swoole\Coroutine\Client;
use EasySwoole\Core\Component\Trigger;
use \Swoole\Coroutine\Redis as SwooleRedis;


class Redis
{
    protected $client = null;
    protected $host;
    protected $port;
    protected $auth = null;
    protected $reConnectTimes = 0;
    function __construct($host,$port,$auth = null)
    {
        $this->client = new SwooleRedis();
    }

    function connect()
    {
        if(!$this->client->connected){
            $this->client->connect($this->host,$this->port);
            if($this->client->connected){
                if($this->auth){
                    $this->client->auth($this->auth);
                }
                $this->reConnectTimes = 0;
                return true;
            }else{
                $this->reConnectTimes++;
                return false;
            }
        }
        return true;
    }

    function client():SwooleRedis
    {
        return $this->client;
    }

    /*
     * 上层请做try
     */
    function exec($method,...$args)
    {
        if(!$this->client->connected && $this->reConnectTimes === 0){
            $this->connect();
            return $this->exec($method,...$args);
        }else if($this->client->connected){
            return $this->client->$method(...$args);
        }else{
            throw new \Exception('redis connect fail');
        }
    }
}