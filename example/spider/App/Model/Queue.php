<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/9
 * Time: 下午3:35
 */

namespace App\Model;


use App\Utility\Db\Redis;

class Queue
{
    const QUEUE_NAME = 'task_list';
    static function set(TaskBean $taskBean){
        return Redis::getInstance()->rPush(self::QUEUE_NAME,$taskBean);
    }
    static function pop(){
        return Redis::getInstance()->lPop(self::QUEUE_NAME);
    }
}