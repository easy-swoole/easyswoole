<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午10:04
 */

namespace EasySwoole\Core\AbstractInterface;

trait Singleton
{
    private static $instance;

    static function getInstance(...$args)
    {
        if(!isset(self::$instance)){
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }
}