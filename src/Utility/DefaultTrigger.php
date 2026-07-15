<?php


namespace EasySwoole\EasySwoole\Utility;


use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerLocation;
use EasySwoole\EasySwoole\Logger;


class DefaultTrigger implements TriggerInterface
{

    public function error($msg, int $errorCode = E_USER_ERROR, TriggerLocation|null $location = null)
    {
        if ($location == null) {
            $location = new TriggerLocation();
            $debugTrace = debug_backtrace();
            $caller = array_shift($debugTrace);
            $location->setLine($caller['line']);
            $location->setFile($caller['file']);
        }

        Logger::getInstance()->log("{$msg} at file:{$location->getFile()} line:{$location->getLine()}", $this->errorMapLogLevel($errorCode), 'trigger');
    }

    public function throwable(\Throwable $throwable,string|null $category = null)
    {
        if(empty($category)){
            $category = 'trigger';
        }
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        Logger::getInstance()->log($msg, LogLevelEnum::ERROR, $category);
    }

    private function errorMapLogLevel(int $errorCode)
    {
        switch ($errorCode) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return LogLevelEnum::ERROR;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                return LogLevelEnum::WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LogLevelEnum::NOTICE;
            default :
                return LogLevelEnum::INFO;
        }
    }
}
