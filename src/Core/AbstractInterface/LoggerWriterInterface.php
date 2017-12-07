<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午5:46
 */

namespace EasySwoole\Core\AbstractInterface;


interface LoggerWriterInterface
{
    function writeLog($obj,$logCategory,$timeStamp);
}