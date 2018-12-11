<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 1:04 PM
 */

namespace EasySwoole\EasySwoole\Actor;


use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine\Channel;

abstract class AbstractActor
{
    private $exit = false;
    private $actorId;
    private $args;
    private $channel;

    abstract function onStart();
    abstract function onCommand(Command $command);
    abstract function onExit();

    function __construct(string $actorId,Channel $channel,$args)
    {
        $this->actorId = $actorId;
        $this->args = $args;
        $this->channel = $channel;
    }

    function exit()
    {
        $this->exit = true;
    }

    function actorId()
    {
        return $this->actorId;
    }

    function getArgs()
    {
        return $this->args;
    }

    function getChannel():Channel
    {
        return $this->channel;
    }

    function __run()
    {
        try{
            $this->onStart();
        }catch (\Throwable $throwable){
            Trigger::getInstance()->throwable($throwable);
        }
        while (1){
            /*
             * 确保全部消息被执行
             */
            if($this->exit && $this->channel->isEmpty()){
                try{
                    $this->onExit();
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
                $this->channel->close();
                break;
            }
            $msg = $this->channel->pop(1);
            if(!empty($msg)){
                $conn = $msg['connection'];
                $com = $msg['command'];
                $reply = $this->onCommand($com);
                fwrite($conn,Protocol::pack(serialize($reply)));
                fclose($conn);
            }
        }
    }
}