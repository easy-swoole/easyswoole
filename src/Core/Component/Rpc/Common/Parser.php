<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午2:00
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Component\Cluster\Config;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;

class Parser implements ParserInterface
{
    protected $services = [];
    function __construct(array $services)
    {
        $this->services = $services;
    }

    public function decode($raw, $client)
    {
        // TODO: Implement decode() method.
        $raw = $this->decodeRawData($raw);
        $json = json_decode($raw,true);
        if(is_array($json)){
            $bean = new Command($json);
            if($this->signatureCheck($bean)){
                if(isset($this->services[$bean->getControllerClass()])){
                    $bean->setControllerClass($this->services[$bean->getControllerClass()]);
                    return $bean;
                }else{
                    return "{$bean->getControllerClass()} server not register";
                }
            }else{
                return 'signature check fail';
            }
        }else{
            return 'package decode fail';
        }
    }

    public function decodeRawData($raw)
    {
        return substr($raw, 4);
    }

    public function encodeRawData($sendStr)
    {
        return pack('N', strlen($sendStr)).$sendStr;
    }

    public function encode(string $sendStr, $client):?string
    {
        // TODO: Implement encode() method.
        return pack('N', strlen($sendStr)).$sendStr;
    }

    /*
     * 集群模式下有服务自动发信，因此需要做数据验证
     * 仅对请求包做签名验证，返回数据做验签无意义。
     * 集群模式请注意机器的时间同步问题
     * 另外不做token使用验证，当发出去的请求包可以被拦截服务，说明服务网络已经出了问题，请运维自行处理
     * 敏感服务，请自己在业务层实现数据加密和验证，例如请求args中加入自己的token，或者做aes。
     */
    public function signature(Command $command)
    {
        if(Config::getInstance()->getEnable()){
            $sid = Config::getInstance()->getServerId();
            $data = $command->toArray([
                'controllerClass','action','args'
            ]);
            ksort($data);
            $s = md5(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).$sid);
            $command->setSignature($s);
            return $command;
        }else{
            return $command;
        }
    }

    public function signatureCheck(Command $command):bool
    {
        if(Config::getInstance()->getEnable()){
            $signatureOld = $command->getSignature();
            $s = $this->signature($command)->getSignature();
            if($s === $signatureOld){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
}