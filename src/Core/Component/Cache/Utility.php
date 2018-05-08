<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午7:13
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Config;
use EasySwoole\Core\Utility\Random;

class Utility
{
    /*
     * 返回文件名
     */
    static function writeFile(string $data):string
    {
        $name = 'cache_'.Random::randStr(12);
        $file = Config::getInstance()->getConf('TEMP_DIR').'/'.$name;
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