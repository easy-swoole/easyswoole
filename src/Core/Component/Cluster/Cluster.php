<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午10:51
 */

namespace EasySwoole\Core\Component\Cluster;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Communicate\Detector;
use EasySwoole\Core\Swoole\Process\ProcessManager;

class Cluster
{
    use Singleton;

    function run()
    {
        if(Config::getInstance()->getEnable()) {
            ProcessManager::getInstance()->addProcess('__CLUSTER',Detector::class);
        }
    }
}