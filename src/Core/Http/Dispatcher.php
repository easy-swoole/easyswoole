<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午5:49
 */

namespace EasySwoole\Core\Http;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;

class Dispatcher
{
    use Singleton;

    private $controllerNameSpacePrefix;
    function __construct()
    {
        $this->controllerNameSpacePrefix = Di::getInstance()->get(SysConst::APP_NAMESPACE).'\\';
    }
}