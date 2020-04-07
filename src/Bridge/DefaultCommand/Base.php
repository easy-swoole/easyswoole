<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/7 0007
 * Time: 15:49
 */

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\BridgeCommand;

abstract class Base
{
    abstract static function initCommand(BridgeCommand $command);
}
