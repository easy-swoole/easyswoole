<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/21
 * Time: 下午10:13
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Socket\Bean\Response;

class Client
{
    private $host;
    private $port;
    function __construct($host,$port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    function call(string $controller,string $action,$args):?array
    {
        $arr = [
            'controller'=>$controller,
            'action'=>$action,
            'args'=>$args
        ];

        $fp = stream_socket_client("tcp://{$this->host}:{$this->port}");
        if($fp){
            $sendStr = json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $data = pack('N', strlen($sendStr)).$sendStr;
            fwrite($fp,$data);
            $data = fread($fp,65533);
            fclose($fp);
            $len = unpack('N',$data);
            $data = substr($data,'4');
            if(strlen($data) != $len[1]){
                throw new \Exception("tcp://{$this->host}:{$this->port} response error data");
            }else{
                return json_decode($data,true);
            }
        }else{
            throw new \Exception("connect to tcp://{$this->host}:{$this->port} fail");
        }
    }
}