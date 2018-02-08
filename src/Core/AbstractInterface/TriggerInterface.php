<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/8
 * Time: 下午1:26
 */

namespace EasySwoole\Core\AbstractInterface;


interface TriggerInterface
{
    public static function error($msg,$file = null,$line = null,$errorCode = E_USER_ERROR);
    public static function throwable(\Throwable $throwable);
}