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
    private static $instance;
    private static $logCategory = 'default';
    static function getInstance($logCategory = 'default'){
        self::$logCategory = $logCategory;
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param $obj
     */
    function log($obj){
        $loggerWriter = Di::getInstance()->get(SysConst::DI_LOGGER_WRITER);
        if($loggerWriter instanceof LoggerWriterInterface){
            $loggerWriter::writeLog($obj,self::$logCategory,time());
        }else{
            $obj = $this->objectToString($obj);
            /*
             * default method to save log
             */
            $str = "time : ".date("y-m-d H:i:s")." message: ".$obj."\n";
            $filePrefix = self::$logCategory."_".date('ym');
            $filePath = ROOT."/Log/{$filePrefix}_log.txt";
            file_put_contents($filePath,$str,FILE_APPEND|LOCK_EX);
        }
    }
    function console($obj,$saveLog = 1){
        $obj = $this->objectToString($obj);
        echo $obj . "\n";
        if($saveLog){
            $this->log($obj);
        }
    }
    private function objectToString($obj){
        if(is_object($obj)){
            if(method_exists($obj,"__toString")){
                $obj = $obj->__toString();
            }else if(method_exists($obj,'jsonSerialize')){
                $obj = json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }else{
                $obj = var_export($obj,true);
            }
        }else if(is_array($obj)){
            $obj = json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        return $obj;
    }
}