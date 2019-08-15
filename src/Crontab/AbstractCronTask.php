<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/11/6
 * Time: 2:42 PM
 */

namespace EasySwoole\EasySwoole\Crontab;

use EasySwoole\Task\AbstractInterface\TaskInterface;

abstract class AbstractCronTask implements TaskInterface
{
    abstract public static function getRule():string ;
    abstract public static function getTaskName():string ;
}