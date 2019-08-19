<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:15
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Event;
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
        $this->callback = new Event();
    }
    
    public function log(?string $msg,string $filename = 'log',int $logLevel = self::LOG_LEVEL_INFO,string $category = 'DEBUG'):string
    {
        $str = $this->logger->log($msg,$filename,$logLevel,$category);
        $calls = $this->callback->all();
        foreach ($calls as $call){
            call_user_func($call,$msg,$filename,$logLevel,$category);
        }
        return $str;
    }

    public function console(?string $msg,string $filename = 'log',int $logLevel = self::LOG_LEVEL_INFO,string $category = 'DEBUG')
    {
        $this->logger->console($msg,$filename,$logLevel,$category);
        $this->log($msg,$filename,$logLevel,$category);
    }


    public function info(?string $msg,string $filename = 'log',string $category = 'DEBUG')
    {
        $this->console($msg,$filename,self::LOG_LEVEL_INFO,$category);
    }

    public function notice(?string $msg,string $filename = 'log',string $category = 'DEBUG')
    {
        $this->console($msg,$filename,self::LOG_LEVEL_NOTICE,$category);
    }

    public function waring(?string $msg,string $filename = 'log',string $category = 'DEBUG')
    {
        $this->console($msg,string $filename,self::LOG_LEVEL_WARNING,$category);
    }

    public function error(?string $msg,string $filename = 'log',string $category = 'DEBUG')
    {
        $this->console($msg,$filename,self::LOG_LEVEL_ERROR,$category);
    }

    public function onLog():Event
    {
        return $this->callback;
    }
}
