<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 3/18/18
 * Time: 4:37 PM
 */

namespace EasySwoole\Core\Swoole\Coroutine\Client;
use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Trigger;
use \Swoole\Coroutine\Redis as SwooleRedis;

class Redis
{
    protected $client = null;
    protected $host;
    protected $port;
    protected $serialize;
    protected $auth = null;
    protected $reConnectTimes = 0;
    protected $errorHandler = null;

    public function __construct($host, $port, $serialize, $auth = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->serialize = $serialize;
        $this->auth = $auth;
        $this->client = new SwooleRedis();
    }

    public function connect()
    {
        if (!$this->client->connected) {
            $this->client->connect($this->host, $this->port, $this->serialize);
            if ($this->client->connected) {
                if ($this->auth) {
                    $this->client->auth($this->auth);
                }
                $this->reConnectTimes = 0;
                return true;
            } else {
                $this->reConnectTimes++;
                return false;
            }
        }
        return true;
    }

    public function client():SwooleRedis
    {
        return $this->client;
    }

    public function exec($method, ...$args)
    {
        if (!$this->client->connected && $this->reConnectTimes === 0) {
            $this->connect();
            return $this->exec($method, ...$args);
        } else if ($this->client->connected) {
            return $this->client->$method(...$args);
        } else {
            if (is_callable($this->errorHandler)) {
                return Invoker::callUserFunc($this->errorHandler,$method,...$args);
            } else {
                throw new \Exception('redis connect fail');
            }
        }
    }

    public function __get($name)
    {
        return $this->client->$name;
    }

    public function setDefer($isDefer = true)
    {
        return $this->client->setDefer($isDefer);
    }

    public function getDefer()
    {
        return $this->client->getDefer();
    }

    public function recv()
    {
        return $this->client->recv();
    }

    public function setErrorHandler(callable $call)
    {
        $this->errorHandler = $call;
    }
}