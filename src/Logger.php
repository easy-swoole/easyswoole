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
use EasySwoole\EasySwoole\AbstractInterface\Log\LoggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;

class Logger
{
    private $logger;

    private $callback;
    private bool $displayConsole = true;

    private array $ignoreCategory = [];


    /** @var array<LogLevelEnum>|null  */
    private array|null $logLevel = null;

    use Singleton;

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->callback = new Event();
    }

    public function onLog(): Event
    {
        return $this->callback;
    }

    public function logLevels(array|null $levelList = null)
    {
        if(is_array($levelList)){
            $this->logLevel = $levelList;
        }

        return $this->logLevel;
    }

    public function displayConsole(bool|null $is = null)
    {
        if($is === null){
            return $this->displayConsole;
        }else{
            $this->displayConsole = $is;
            return $this;
        }
    }

    public function ignoreCategory(array|null $arr = null)
    {
        if ($arr === null) {
            return $this->ignoreCategory;
        } else {
            $this->ignoreCategory = $arr;
            return $this;
        }
    }

    public function log(string $msg, LogLevelEnum $logLevel = LogLevelEnum::INFO, string|null $category = null):bool
    {
        if(!empty($this->logLevel) && !in_array($logLevel, $this->logLevel)){
            return false;
        }

        if (!empty($category) && in_array($category, $this->ignoreCategory)) {
            return false;
        }

        $this->console($msg, $logLevel, $category);

        return $this->logger->log($msg, $logLevel, $category);
    }

    public function console(string $msg, LogLevelEnum $logLevel = LogLevelEnum::INFO, string|null $category = null):bool
    {
        if($this->displayConsole){
            return $this->logger->console($msg, $logLevel, $category);
        }else{
            return false;
        }
    }

    public function info(string $msg, string|null $category = null):bool
    {
        return $this->log($msg, LogLevelEnum::INFO, $category);
    }

    public function notice(string $msg, string|null $category = null):bool
    {
        return $this->log($msg, LogLevelEnum::NOTICE, $category);
    }

    public function warning(string $msg, string|null $category = null):bool
    {
        return $this->log($msg, LogLevelEnum::WARNING, $category);
    }

    public function error(string $msg, string|null $category = null):bool
    {
        return $this->log($msg, LogLevelEnum::ERROR, $category);
    }
}
