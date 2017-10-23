<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午2:45
 */

namespace Core\Component\Socket\AbstractInterface;


use Core\Component\Socket\Common\CommandList;

abstract class AbstractCommandRegister
{
    abstract function register(CommandList $commandList);
}