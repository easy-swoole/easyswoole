<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-07
 * Time: 14:45
 */

namespace EasySwoole\EasySwoole\Console;


use Swoole\Coroutine\Client as CoClient;

class Client
{
    private $host;
    private $port;
    private $client = null;

    function __construct($host,$port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    function getClient():?CoClient
    {
        return $this->client;
    }

    function connect():bool
    {
        $this->client = new CoClient(SWOOLE_SOCK_TCP);
        $this->client->set(
            [
                "open_eof_split" => true,
                'package_eof' => "\r\n",
            ]
        );
        return $this->client->connect($this->host, $this->port, 0.5);
    }

    public function sendCommand(string $commandLine):bool
    {
        if($this->client instanceof CoClient && $this->client->isConnected()){
            $this->client->send($commandLine."\r\n");
            return true;
        }else{
            return false;
        }
    }

    public function recv(float $timeout = -1)
    {
        if($this->client instanceof CoClient && $this->client->isConnected()){
            $data = $this->client->recv($timeout);
            if($data !== false){
                return trim($data);
            }
        }
        return false;
    }
}
