<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午2:00
 */

namespace EasySwoole\Core\Component\Rpc\Common;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Server;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Common\CommandBean;

class Parser implements ParserInterface
{
    protected $services = [];
    protected $encoder = null;
    function __construct(array $services)
    {
        $this->services = $services;
        $encrypt = Server::getInstance()->encrypt();
        if(!empty($encrypt)){
            $token = Server::getInstance()->token();
            $this->encoder = new Openssl($token,$encrypt);
        }
    }

    public function decode($raw, $client)
    {
        // TODO: Implement decode() method.
        $raw = self::unPack($raw);
        if($this->encoder){
            $raw = $this->encoder->decrypt($raw);
        }
        $json = json_decode($raw,true);
        if(is_array($json)){
            $bean = new CommandBean($json);
            if(isset($this->services[$bean->getControllerClass()])){
                $bean->setControllerClass($this->services[$bean->getControllerClass()]);
                return $bean;
            }else{
                return "{$bean->getControllerClass()} server not register";
            }
        }else{
            return 'package decode fail';
        }
    }

    public static function unPack($raw)
    {
        return substr($raw, 4);
    }

    public static function pack($sendStr)
    {
        return pack('N', strlen($sendStr)).$sendStr;
    }

    public function encode(string $sendStr, $client,$commandBean):?string
    {
        // TODO: Implement encode() method.
        if($this->encoder){
            $sendStr = $this->encoder->encrypt($sendStr);
        }
        $data = self::pack($sendStr);
        return $data;
    }
}