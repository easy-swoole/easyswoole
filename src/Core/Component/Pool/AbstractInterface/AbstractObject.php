<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/3
 * Time: 下午2:16
 */

namespace EasySwoole\Core\Component\Pool\AbstractInterface;


abstract class AbstractObject
{
    protected abstract function gc();
    //使用后,free的时候会执行
    abstract function initialize();

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->gc();
    }
}