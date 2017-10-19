<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午2:46
 */

namespace Core\Component\RPC\Common;


abstract class AbstractPackageEncoder
{
    abstract function encode($rawData);
}