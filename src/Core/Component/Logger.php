<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/1
 * Time: 上午1:32
 */

namespace Core\Component;


use Core\AbstractInterface\LoggerWriterInterface;
use Core\Component\Sys\SysConst;

class Logger
{
    private static $instance = array();
    private $logCategory = 'default';

    static function getInstance($logCategory = 'default'){
        if(!isset(self::$instance[$logCategory])){
            //这样做纯属为了IDE提示
            $instance = new static($logCategory);
            self::$instance[$logCategory] = $instance;
        }else{
            $instance = self::$instance[$logCategory];
        }
        return $instance;
    }

    function __construct($logCategory)
    {
        $this->logCategory = $logCategory;
    }

    /**
     * @param $obj
     */
    function log($obj){
        $loggerWriter = Di::getInstance()->get(SysConst::DI_LOGGER_WRITER);
        if($loggerWriter instanceof LoggerWriterInterface){
            $loggerWriter::writeLog($obj,$this->logCategory,time());
        }else{
            $obj = $this->objectToString($obj);
            /*
             * default method to save log
             */
            $str = "time : ".date("y-m-d H:i:s")." message: ".$obj."\n";
            $filePrefix = $this->logCategory."_".date('ym');
            $filePath = ROOT."/Log/{$filePrefix}.log";
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