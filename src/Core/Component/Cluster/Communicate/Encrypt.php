<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/8
 * Time: 下午3:16
 */

namespace EasySwoole\Core\Component\Cluster\Communicate;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Config;
use EasySwoole\Core\Component\Openssl;

class Encrypt
{
    use Singleton;

    private $openssl;

    function __construct()
    {
        $this->openssl = new Openssl(Config::getInstance()->getToken());
    }

    function getEncoder()
    {
        return $this->openssl;
    }
}