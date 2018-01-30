<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午10:51
 */

namespace EasySwoole\Core\Component\Cluster;


use EasySwoole\Core\AbstractInterface\Singleton;

class Cluster
{
    use Singleton;

    function run()
    {
        if (Config::getInstance()->getEnable()) {
            //执行进程注册
        }
    }
}