<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/1
 * Time: 上午1:32
 */

namespace Core\Component;


use Core\AbstractInterface\LoggerWriterInterface;

class Logger
{
    /**
     * @param $str
     */
    static function log($str){
        $loggerWriter = Di::getInstance()->get(SysConst::DI_LOGGER_WRITER);
        if($loggerWriter instanceof LoggerWriterInterface){
            $loggerWriter::writeLog($str,time());
        }else{
            /*
             * default method to save log
             */
            $str = "time : ".date("y-m-d H:i:s")." message: ".$str."\n";
            $filePrefix = date('ym');
            $filePath = ROOT."/{$filePrefix}_log.txt";
            file_put_contents($filePath,$str,FILE_APPEND|LOCK_EX);
        }
    }
    static function console($str,$saveLog = 1){
        echo $str . "\n";
        if($saveLog){
            self::log($str);
        }
    }
}