<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午7:13
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Utility\Random;

class Utility
{
    /*
     * 返回文件名
     */
    static function writeFile(string $data):string
    {
        $name = 'cache_'.Random::randStr(12);
        $file = Di::getInstance()->get(SysConst::DIR_TEMP).'/'.$name;
        file_put_contents($file,$data);
        return $file;
    }

    static function readFile($file,$deleteFile = true):?string
    {
        if(file_exists($file)){
            $data = file_get_contents($file);
            if($deleteFile){
                unlink($file);
            }
            return $data;
        }else{
            return null;
        }
    }

}