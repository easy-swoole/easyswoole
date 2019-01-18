<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/21
 * Time: 下午10:13
 */

namespace EasySwoole\EasySwoole\Console;

/*
 * 注意，该客户端仅供cli或者独立process使用
 */

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

    function close():bool
    {
        if($this->client instanceof \swoole_client && $this->client->isConnected()){
            $this->client->close();
            $this->client = null;
            return true;
        }else if($this->client instanceof \swoole_client){
            //若服务端主动断开的时候
            $this->client = null;
            return true;
        }else{
            return false;
        }
    }

    function getClient():?\swoole_client
    {
        return $this->client;
    }

    function connect():bool
    {
        $this->close();

        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->set(
            [
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]
        );
        $this->client->on("connect", function(\swoole_client $cli) {
            fwrite(STDOUT, "connect to tcp://{$this->host}:{$this->port} succeed \n");
        });

        $this->client->on("close", function($cli){
            $this->close();
            fwrite(STDOUT,"tcp://{$this->host}:{$this->port} disconnect \n");
            swoole_event_del(STDIN);
        });

        $this->client->on("error", function($cli){
            $this->close();
            fwrite(STDOUT,"connection tcp://{$this->host}:{$this->port} error \n");
            swoole_event_del(STDIN);
        });

        $this->client->on("receive", function($cli, $data) {
            $str = unserialize(ConsoleProtocolParser::unpack($data));
            echo $str . PHP_EOL;
        });
        return $this->client->connect($this->host, $this->port, 0.5);
    }

    public function sendCommand(string $commandLine):bool
    {
        if($this->client instanceof \swoole_client && $this->client->isConnected()){
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
}