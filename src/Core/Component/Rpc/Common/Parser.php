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
    protected $serviceClass;
    function __construct(string $serviceClass)
    {
        $this->serviceClass = $serviceClass;
    }

    public function decode($raw, array $args):?CommandBean
    {
        // TODO: Implement decode() method.
        $raw = substr($raw, 4);
        $bean = \swoole_serialize::unpack($raw);
        if(!$bean instanceof CommandBean){
            $bean = new CommandBean();
        }
        $bean->setControllerClass($this->serviceClass);
        return $bean;

    }

    public function encode(?CommandBean $raw, array $args):?string
    {
        // TODO: Implement encode() method.
        if(!$raw instanceof CommandBean){
            $raw = new CommandBean();
        }
        if($raw->getStatus() === null){
            $raw->setStatus(Status::OK);
        }
        $sendStr = \swoole_serialize::pack($raw);
        return pack('N', strlen($sendStr)).$sendStr;
    }
}