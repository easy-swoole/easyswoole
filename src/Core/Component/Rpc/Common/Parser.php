<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午2:00
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Common\CommandBean;

class Parser implements ParserInterface
{
    protected $services = [];
    function __construct(array $services)
    {
        $this->services = $services;
    }

    public function decode($raw, $client):?CommandBean
    {
        // TODO: Implement decode() method.
        $raw = substr($raw, 4);
        $bean = \swoole_serialize::unpack($raw);
        if(!$bean instanceof CommandBean){
           return null;
        }
        //controllerClass作为服务名称
        if(isset($this->services[$bean->getControllerClass()])){
            $bean->setControllerClass($this->services[$bean->getControllerClass()]);
        }else{
            return null;
        }
        return $bean;

    }

    public function encode(?CommandBean $raw, $client):?string
    {
        // TODO: Implement encode() method.
        if($raw->getStatus() === null){
            $raw->setStatus(Status::OK);
        }
        $sendStr = \swoole_serialize::pack($raw);
        return pack('N', strlen($sendStr)).$sendStr;
    }
}