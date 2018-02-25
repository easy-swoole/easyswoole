<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/25
 * Time: 上午11:37
 */

namespace EasySwoole\Core\Swoole\PipeMessage;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Event;
use EasySwoole\Core\Component\SuperClosure;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\AbstractAsyncTask;


class EventRegister extends Event
{
    use Singleton;
    const TASK = 'TASK';
    function __construct(array $allowKeys = null)
    {
        parent::__construct($allowKeys);
        $this->add(self::TASK,function ($fromId,$taskObj){
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->run($taskObj->getData(),ServerManager::getInstance()->getServer()->worker_id,$fromId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }else if($taskObj instanceof SuperClosure){
                try{
                    $taskObj();
                }catch (\Throwable $throwable){
                    Trigger::throwable($throwable);
                }
            }
        });
    }
}