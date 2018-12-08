<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/6
 * Time: 11:10 PM
 */

namespace EasySwoole\EasySwoole\FastCache;

use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Spl\SplArray;
use Swoole\Process;

class CacheProcess extends AbstractProcess
{
    /*
     * @var $splArray SplArray
     */
    protected $splArray;
    public function run(Process $process)
    {
        $this->splArray = new SplArray();
        // TODO: Implement run() method.
        go(function (){
            $index = $this->getArg('index');
            $sockfile = EASYSWOOLE_TEMP_DIR."/server{$index}.sock";
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
                    $com = new Package();
                    stream_set_timeout($conn,2);
                    //先取4个字节的头
                    $header = fread($conn,4);
                    if(strlen($header) == 4){
                        $allLength = Protocol::packDataLength($header);
                        $data = fread($conn,$allLength );
                        if(strlen($data) == $allLength){
                            //开始数据包+命令处理，并返回数据
                            $fromPackage = unserialize($data);
                            if($fromPackage instanceof Package){
                                switch ($fromPackage->getCommand())
                                {
                                    case 'set':{
                                        $com->setValue(true);
                                        $this->splArray->set($fromPackage->getKey(),$fromPackage->getValue());
                                        break;
                                    }
                                    case 'get':{
                                        $com->setValue($this->splArray->get($fromPackage->getKey()));
                                        break;
                                    }
                                    case 'unset':{
                                        $com->setValue(true);
                                        $this->splArray->unset($fromPackage->getKey());
                                        break;
                                    }


                                }
                            }
                        }
                    }
                    fwrite($conn,Protocol::pack(serialize($com)));
                    fclose($conn);
                }
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