<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午2:36
 */

namespace EasySwoole\Core\Component\Spl;


use EasySwoole\Core\Component\Lib\Stream;

class SplString extends Stream
{
    function __construct(string $str = null)
    {
        parent::__construct($str);
    }
}