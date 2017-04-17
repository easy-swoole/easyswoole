<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:44
 */

namespace Core\AbstractInterface;


use Core\Component\Spl\SplError;

interface ErrorHandlerInterface
{
    function handler(SplError $error);
    function display(SplError $error);
    function log(SplError $error);
}