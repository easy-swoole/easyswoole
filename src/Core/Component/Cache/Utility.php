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
    static function isOutOfLength($data)
    {
        $str = \swoole_serialize::pack($data);
        $len = strlen($str);
        if($len > 28*1024){
            return $str;
        }else{
            return false;
        }
    }

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

    static function readProcessFileData(int $processNum):SplArray
    {
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$processNum}.data";
        if(file_exists($file)){
            $content = file_get_contents($file);
            if(!empty($content)){
                $array = \swoole_serialize::unpack($content);
                if(is_array($array)){
                    return new SplArray($array);
                }
            }
        }
        return new SplArray();
    }

    static function writeProcessFileData(int $processNum,SplArray $array):bool
    {
        $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$processNum}.data";
        return (bool)file_put_contents($file,\swoole_serialize::pack($array->getArrayCopy()),LOCK_EX);
    }
}