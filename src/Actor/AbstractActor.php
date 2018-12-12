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
    private $hasDoExit = false;
    private $actorId;
    private $args;
    private $channel;
    private $tickList = [];

    abstract function onStart();
    abstract function onMessage($arg);
    abstract function onExit();

    final function __construct(string $actorId,Channel $channel,$args)
    {
        $this->actorId = $actorId;
        $this->args = $args;
        $this->channel = $channel;
    }

    function actorId()
    {
        return $this->actorId;
    }

    /*
     * 请用该方法来添加定时器，方便退出的时候自动清理定时器
     */
    function tick($time,callable $callback)
    {
        $id = swoole_timer_tick($time,$callback);
        $this->tickList[$id] = $id;
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
        while (1 && !$this->hasDoExit){
            $msg = $this->channel->pop(1);
            if(!empty($msg)){
                $conn = $msg['connection'];
                $msg = $msg['msg'];
                if($msg === 'exit'){
                    try{
                        //清理定时器
                        foreach ($this->tickList as $tickId){
                            swoole_timer_clear($tickId);
                        }
                        $this->hasDoExit = true;
                        $reply = $this->onExit();
                        if($reply === null){
                            $reply = true;
                        }
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                    $this->channel->close();
                }else{
                    $reply = $this->onMessage($msg);
                }
                fwrite($conn,Protocol::pack(serialize($reply)));
                fclose($conn);
            }
        }
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->hasDoExit == false){
            $this->onExit();
        }
    }
}