<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/9
 * Time: 下午7:04
 */

namespace Core\AbstractInterface;


interface ExceptionHandlerInterface
{
    function handler(\Exception $exception);
    function display(\Exception $exception);
    function log(\Exception $exception);
}