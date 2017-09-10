<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/10
 * Time: 下午3:51
 */

namespace Core\Component\IO;


class FileIO extends Stream
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