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
        $conf = Config::getInstance();
        if($conf->getEnable()) {
            ProcessManager::getInstance()->addProcess("{$conf->getServerName()}_Cluster_Detector",Detector::class);
        }
    }

    function config():Config
    {
        return Config::getInstance();
    }
}