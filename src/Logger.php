<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:15
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    private $logger;

    private $callback;

    use Singleton;

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function log(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'DEBUG'):string
    {
        $str = $this->logger->log($msg,$logLevel,$category);
        if($this->callback){
            call_user_func($this->callback,$msg,$logLevel,$category);
        }
        return $str;
    }

    public function console(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'DEBUG')
    {
        $this->logger->console($msg,$logLevel,$category);
        $this->log($msg,$logLevel,$category);
    }


    public function info(?string $msg,string $category = 'DEBUG')
    {
        $this->console($msg,self::LOG_LEVEL_INFO,$category);
    }

    public function notice(?string $msg,string $category = 'DEBUG')
    {
        $this->console($msg,self::LOG_LEVEL_NOTICE,$category);
    }

    public function waring(?string $msg,string $category = 'DEBUG')
    {
        $this->console($msg,self::LOG_LEVEL_WARNING,$category);
    }

    public function error(?string $msg,string $category = 'DEBUG')
    {
        $this->console($msg,self::LOG_LEVEL_ERROR,$category);
    }

    public function onLog(callable $call)
    {
        $this->callback = $call;
    }
}