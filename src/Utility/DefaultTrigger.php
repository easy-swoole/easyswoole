<?php


namespace EasySwoole\EasySwoole\Utility;


use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

class DefaultTrigger implements TriggerInterface
{

    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        if ($location == null) {
            $location = new Location();
            $debugTrace = debug_backtrace();
            $caller = array_shift($debugTrace);
            $location->setLine($caller['line']);
            $location->setFile($caller['file']);
        }

        Logger::getInstance()->log("{$msg} at file:{$location->getFile()} line:{$location->getLine()}", $this->errorMapLogLevel($errorCode), 'trigger');
    }

    public function throwable(\Throwable $throwable)
    {
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        Logger::getInstance()->log($msg, LoggerInterface::LOG_LEVEL_ERROR, 'trigger');
    }

    private function errorMapLogLevel(int $errorCode)
    {
        switch ($errorCode) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return LoggerInterface::LOG_LEVEL_ERROR;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                return LoggerInterface::LOG_LEVEL_WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LoggerInterface::LOG_LEVEL_NOTICE;
            default :
                return LoggerInterface::LOG_LEVEL_INFO;
        }
    }
}
