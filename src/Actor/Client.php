<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 1:33 PM
 */

namespace EasySwoole\EasySwoole\Actor;


class Client
{
    private $client = null;

    function __construct(string $unixSock)
    {
        $this->client = new \Swoole\Coroutine\Client(SWOOLE_UNIX_STREAM);
        $this->client->set(
            [
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]
        );
        $this->client->connect($unixSock,0.1);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->client->isConnected()){
            $this->client->close();
        }
    }

    function send(string $rawData)
    {
        if($this->client->isConnected()){
            return $this->client->send(Protocol::pack($rawData));
        }else{
            return false;
        }
    }

    function recv(float $timeout = 0.1)
    {
        if($this->client->isConnected()){
            $ret = $this->client->recv($timeout);
            if(!empty($ret)){
                return Protocol::unpack($ret);
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
}