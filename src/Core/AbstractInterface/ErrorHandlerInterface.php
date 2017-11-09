<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:44
 */

namespace Core\AbstractInterface;

interface ErrorHandlerInterface
{
    function handler($msg,$trace);
    function display($msg,$trace);
    function log($msg,$trace);
}