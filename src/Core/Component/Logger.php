<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午11:28
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Config;
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
        $this->defaultDir = Config::getInstance()->getConf('LOG_DIR');
    }

    public function log(string $str,$category = 'default'):Logger
    {
        if($this->loggerWriter instanceof LoggerWriterInterface){
            $this->loggerWriter->writeLog($str,$category,time());
        }else{
            $str = date("y-m-d H:i:s").":{$str}\n";
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
    }

    public function consoleWithTrace(string $str,$saveLog = 1)
    {
        $debug = $this->debugInfo();
        $debug = "file[{$debug['file']}] function[{$debug['function']}] line[{$debug['line']}]";
        $str = "{$debug} message: [{$str}]";
        echo $str . "\n";
        if($saveLog){
            $this->log($str,'console');
        }
    }

    public function logWithTrace(string $str,$category = 'default')
    {
        $debug = $this->debugInfo();
        $debug = "file[{$debug['file']}] function[{$debug['function']}] line[{$debug['line']}]";
        $this->log("{$debug} message: [{$str}]",$category);
    }

    private function debugInfo() {
        $trace = debug_backtrace();
        $file = $trace[1]['file'];
        $line = $trace[1]['line'];
        $func = isset($trace[2]['function']) ? $trace[2]['function'] : 'unKnown';
        return [
            'file'=>$file,
            'line'=>$line,
            'function'=>$func
        ];
    }
}