<?php

namespace EasySwoole\EasySwoole\Utility;

use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\AbstractInterface\Log\LoggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;

class DefaultLogger implements LoggerInterface
{
    function __construct(
        protected string $logDir,
        protected string|null $prefix,
    ){}
    function log(string $msg, LogLevelEnum $logLevel = LogLevelEnum::INFO, ?string $category = null): bool
    {
        $levelStr = $logLevel->name;
        $date = date('Ym');
        if(!empty($this->prefix)){
            $filePath = $this->logDir."/{$this->prefix}.log_{$date}.log";
        }else{
            $filePath = $this->logDir."/log_{$date}.log";
        }
        $time = date('Y-m-d H:i:s');
        if(empty($category)){
            $category = 'debug';
        }
        $str = "[{$time}][{$levelStr}][{$category}]:{$msg}\n";
        file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
        return true;
    }

    function console(string $msg, LogLevelEnum $logLevel = LogLevelEnum::INFO, ?string $category = null):bool
    {
        $levelStr = $logLevel->name;
        $time = date('Y-m-d H:i:s');
        if(empty($category)){
            $category = 'debug';
        }
        $str = "[{$time}][{$levelStr}][{$category}]:{$msg}\n";

        switch($logLevel){
            case LogLevelEnum::INFO:{
                $str = Color::info($str);
                break;
            }
            case LogLevelEnum::NOTICE:{
                $str = Color::notice($str);
                break;
            }
            case LogLevelEnum::WARNING:{
                $str = Color::warning($str);
                break;
            }
            case LogLevelEnum::ERROR:{
                $str = Color::error($str);
                break;
            }
        }

        printf($str);
        return true;
    }
}