<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/6
 * Time: 11:10 PM
 */

namespace EasySwoole\EasySwoole\FastCache;


use EasySwoole\EasySwoole\Swoole\Memory\ChannelManager;
use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine\Channel;
use Swoole\Process;

class CacheProcess extends AbstractProcess
{

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        go(function (){
            $sockfile = EASYSWOOLE_ROOT."/server.sock";
            if (file_exists($sockfile))
            {
                unlink($sockfile);
            }
            $socket = stream_socket_server("unix://$sockfile", $errno, $errstr);
            if (!$socket)
            {
                Trigger::getInstance()->error($errstr);
                return;
            }
            while (1){
                $conn = stream_socket_accept($socket,-1);
                if($conn){
                    stream_set_timeout($conn,2);
                    //先取4个字节的头
                    $header = fread($conn,4);
                    if(strlen($header) == 4){
                        $allLength = Protocol::packDataLength($header);
                        $data = fread($conn,$allLength );
                        if(strlen($data) == $allLength){
                            //开始数据包+命令处理，并返回数据
                            fwrite($conn,Protocol::pack(time()));
                            fclose($conn);
                        }
                    }
                }

                  /*
                   * stream_select暂时无法被协程化
                    $read = [$socket];
                    $mod_fd = stream_select($read, $write, $except , 1);
                    var_dump('select');
                    if ($mod_fd === FALSE) {
                        return;
                    }
                    while ($mod_fd > 0){
                        $conn = stream_socket_accept($socket);
                        stream_set_blocking($conn,0);
                        $data = fread($conn,1024);
                        var_dump($data);
                        fwrite($conn,time());
                        $mod_fd--;
                    }
                   */
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
        file_put_contents(EASYSWOOLE_ROOT.'/sh.txt',date('h:i:s'));
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }
}