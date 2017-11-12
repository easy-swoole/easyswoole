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
    function handler($msg,$file = null,$line = null,$errorCode = null,$trace);
    function display($msg,$file = null,$line = null,$errorCode = null,$trace);
    function log($msg,$file = null,$line = null,$errorCode = null,$trace);
}