<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/8
 * Time: 上午11:07
 */

namespace EasySwoole\Core\Swoole\Coroutine\AbstractInterface;


interface PoolObject
{
    function initialize();
    function gc();
}