<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/5
 * Time: 下午2:34
 */

namespace EasySwoole\Core\Socket\AbstractInterface;


interface ExceptionHandler
{
    public static function handler(\Throwable $throwable,string $data,$client):?string ;
}