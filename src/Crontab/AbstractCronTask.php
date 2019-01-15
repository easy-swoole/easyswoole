<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/11/6
 * Time: 2:42 PM
 */

namespace EasySwoole\EasySwoole\Crontab;


use EasySwoole\EasySwoole\Swoole\Task\QuickTaskInterface;

abstract class AbstractCronTask implements QuickTaskInterface
{
    abstract public static function getRule():string ;
    abstract public static function getTaskName():string ;
}