<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/1
 * Time: 下午4:35
 */

namespace EasySwoole\Core\Component\Rpc\Common;


class Signature
{
    private $key;
    private $ttl ;
    //集群模式时，启用数据验签
    function __construct($key,$ttl = 10)
    {
        $this->key = $key;
        $this->ttl = $ttl;
    }
}