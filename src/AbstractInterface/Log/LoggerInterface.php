<?php

namespace EasySwoole\EasySwoole\AbstractInterface\Log;

interface LoggerInterface
{
    function log(string $msg,LogLevelEnum $logLevel = LogLevelEnum::INFO,string|null $category = null):bool;
    function console(string $msg,LogLevelEnum $logLevel = LogLevelEnum::INFO,string|null $category = null):bool;
}