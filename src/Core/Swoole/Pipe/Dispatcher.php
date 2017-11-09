<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 上午2:28
 */

namespace Core\Swoole\Pipe;


use Core\Component\Di;
use Core\Component\SysConst;

class Dispatcher
{
    private $commandList;
    private static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Dispatcher();
        }
        return self::$instance;
    }

    function __construct()
    {
        $this->commandList = new CommandList();
        $register = Di::getInstance()->get(SysConst::PIPE_COMMAND_REGISTER);
        if($register instanceof AbstractCommandRegister){
            $register->register($this->commandList);
        }
    }

    /*
     * $onCommandWorker 当前（目标）收到信息进程
     * $fromProcessId 来自哪个进程的id
     */
    function dispatch($onCommandWorker, $fromProcessId,$data){
        $arr = json_decode($data,1);
        $arr = is_array($arr) ? $arr : [];
        $message = new Message($arr);
        $handler = $this->commandList->getHandler($message->getCommand());
        if(is_callable($handler)){
            try{
                call_user_func_array($handler,array(
                    $onCommandWorker,$fromProcessId,$message
                ));
            }catch (\Exception $exception){
                trigger_error($exception->getTraceAsString());
            }
        }
    }

}