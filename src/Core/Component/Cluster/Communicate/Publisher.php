<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/6
 * Time: 下午2:27
 */

namespace EasySwoole\Core\Component\Cluster\Communicate;


use EasySwoole\Core\Component\Cluster\Config;
use EasySwoole\Core\Component\Cluster\NetWork\Udp;
use EasySwoole\Core\Component\Cluster\Server\NodeBean;
use EasySwoole\Core\Component\Trigger;

class Publisher
{
    static function sendTo(CommandBean $commandBean,NodeBean $nodeBean)
    {

    }

    static function broadcast(CommandBean $commandBean)
    {
        $list = Config::getInstance()->getBroadcastAddress();
        if(is_array($list) && !empty($list)){
            foreach ($list as $item){
                $item = explode(':',$item);
                $str = Encrypt::getInstance()->getEncoder()->encrypt($commandBean->__toString());
                Udp::broadcast($str,$item[1],$item[0]);
            }
        }else{
            Trigger::error('cluster broadcast address illegal or empty',__FILE__,__LINE__,E_WARNING);
        }
    }
}