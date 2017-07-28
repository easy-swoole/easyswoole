<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/28
 * Time: 下午7:32
 */

namespace App\Model\Log;


class Writer
{
    protected static $file = ROOT."/log.txt";
    static function write($str){
        file_put_contents(self::$file,$str,FILE_APPEND);
    }
}