<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/7
 * Time: 上午11:56
 */

namespace EasySwoole\Core\Component\Spl;


class SplFileStream extends SplStream
{
    function __construct($file,$mode = 'c+')
    {
        $fp = fopen($file,$mode);
        parent::__construct($fp);
    }

    function lock($mode = LOCK_EX){
        return flock($this->getStreamResource(),$mode);
    }

    function unlock($mode = LOCK_UN){
        return flock($this->getStreamResource(),$mode);
    }
}