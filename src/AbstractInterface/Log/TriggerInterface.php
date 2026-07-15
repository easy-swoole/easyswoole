<?php

namespace EasySwoole\EasySwoole\AbstractInterface\Log;


interface TriggerInterface
{
    public function error($msg,int $errorCode = E_USER_ERROR,TriggerLocation|null $location = null);
    public function throwable(\Throwable $throwable,string|null $category = null);
}