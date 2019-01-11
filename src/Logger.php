<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:15
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Console\TcpService;
use EasySwoole\Trace\AbstractInterface\LoggerInterface;

class Logger implements LoggerInterface
{
    private $logger;
    use Singleton;

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function log(string $str, $logCategory, int $timestamp = null)
    {
        // TODO: Implement log() method.
        $this->logger->log($str,$logCategory,$timestamp);
    }

    public function console(string $str, $category = null, $saveLog = true)
    {
        // TODO: Implement console() method.
        $this->logger->console($str,$category,$saveLog);
    }
}