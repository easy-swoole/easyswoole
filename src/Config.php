<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/3
 * Time: 下午5:48
 */

namespace EasySwoole;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\ServerManager;


class Config
{
    private $conf;

    use Singleton;

    final public function __construct()
    {
        //请在bin文件中执行 install命令，将Resource/Config.php释放至ROOT
        $data = require ROOT.'/Config.php';
        $this->conf = new SplArray($data);
    }

    public function getConf($keyPath)
    {
        return $this->conf->get($keyPath);
    }

    /*
      * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
    */
    public function setConf($keyPath,$data):void
    {
        $this->conf->set($keyPath,$data);
    }
}