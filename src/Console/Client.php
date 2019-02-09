<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/21
 * Time: 下午10:13
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
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]
        );
        return $this->client->connect($this->host, $this->port, 0.5);
    }

    public function sendCommand(string $commandLine):bool
    {
        if($this->client instanceof CoClient && $this->client->isConnected()){
            $commandList = $this->commandParser($commandLine);
            $this->client->send(ConsoleProtocolParser::pack(serialize($commandList)));
            return true;
        }else{
            return false;
        }
    }

    private function commandParser(string $data):array
    {
        $list = explode(' ',$data);
        $ret = [];
        foreach ($list as $item){
            if(!empty($item)){
                array_push($ret,$item);
            }
        }
        return $ret;
    }

    public function recv(float $timeout = -1)
    {
        if($this->client instanceof CoClient && $this->client->isConnected()){
            $data = $this->client->recv($timeout);
            if($data !== false){
                $data = ConsoleProtocolParser::unpack($data);
                return unserialize($data);
            }
        }
        return false;
    }
}