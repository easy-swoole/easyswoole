<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:44
 */

namespace Core\AbstractInterface;


use Core\Component\Spl\Error;

interface ErrorHandlerInterface
{
    function handler(Error $error);
    function display(Error $error);
    function log(Error $error);
}