<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午11:28
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\AbstractInterface\LoggerWriterInterface;
use EasySwoole\Core\AbstractInterface\Singleton;

class Logger
{
    use Singleton;

    private $loggerWriter;
    private $defaultDir;

    function __construct()
    {
        $logger = Di::getInstance()->get(SysConst::LOGGER_WRITER);
        if($logger instanceof LoggerWriterInterface){
            $this->loggerWriter = $logger;
        }
        $this->defaultDir = Di::getInstance()->get(SysConst::DIR_LOG);

    }

    public function log(string $str,$category = 'default'):Logger
    {
        if($this->loggerWriter instanceof LoggerWriterInterface){
            $this->loggerWriter->writeLog($str,$category,time());
        }else{
            /*
             * default method to save log
             */
            $str = "time : ".date("y-m-d H:i:s")." message: ".$str."\n";
            $filePrefix = $category."_".date('ym');
            $filePath = $this->defaultDir."/{$filePrefix}.log";
            file_put_contents($filePath,$str,FILE_APPEND|LOCK_EX);
        }
        return $this;
    }

    public function console(string $str,$saveLog = 1){
        echo $str . "\n";
        if($saveLog){
            $this->log($str,'console');
        }
        return $this;
    }
}