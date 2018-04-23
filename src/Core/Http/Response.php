<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Http\Message\Response as MessageResponse;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Message\Utility;
use EasySwoole\Core\Utility\Curl\Cookie;


class Response extends MessageResponse
{
    private $response;
    const STATUS_NOT_END = 0;
    const STATUS_LOGICAL_END = 1;
    const STATUS_REAL_END = 2;
    const STATUS_RESPONSE_DETACH = 3;

    private $sendFile = null;

    private $isEndResponse = self::STATUS_NOT_END;//1 逻辑end  2真实end 3分离响应

    final public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
        parent::__construct();
    }

    function end($status = self::STATUS_LOGICAL_END){
        $this->isEndResponse = self::STATUS_LOGICAL_END;
        if($status == self::STATUS_REAL_END){
            $this->response->end();
        }
    }

    function response():bool
    {
        if($this->isEndResponse <= self::STATUS_REAL_END){
            //结束处理
            $status = $this->getStatusCode();
            $this->response->status($status);
            $headers = $this->getHeaders();
            foreach ($headers as $header => $val){
                foreach ($val as $sub){
                    $this->response->header($header,$sub);
                }
            }
            $cookies = $this->getCookies();
            foreach ($cookies as $cookie){
                $this->response->cookie($cookie->getName(),$cookie->getValue(),$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$cookie->isSecure(),$cookie->isHttpOnly());
            }
            $write = $this->getBody()->__toString();
            if($write !== ''){
                $this->response->write($write);
            }

            if($this->sendFile != null){
                $this->response->sendfile($this->sendFile);
            }

            $this->getBody()->close();
            $this->end(self::STATUS_REAL_END);
            return true;
        }else{
            return false;
        }
    }

    function isEndResponse()
    {
        return $this->isEndResponse;
    }

    function write(string $str){
        if(!$this->isEndResponse()){
            $this->getBody()->write($str);
            return true;
        }else{
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $file = $caller['file'];
            $line = $caller['line'];
            Trigger::error("response has end at status {$this->isEndResponse}",$file,$line);
            return false;
        }
    }

    function redirect($url,$status = Status::CODE_MOVED_TEMPORARILY){
        if(!$this->isEndResponse()){
            //仅支持header重定向  不做meta定向
            $this->withStatus($status);
            $this->withHeader('Location',$url);
        }else{
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $file = $caller['file'];
            $line = $caller['line'];
            Trigger::error("response has end at status {$this->isEndResponse}",$file,$line);
        }
    }

    /*
     * 目前swoole不支持同键名的header   因此只能通过别的方式设置多个cookie
     */
    public function setCookie($name, $value = null, $expire = null, $path = '/', $domain = '', $secure = false, $httponly = false){
        if(!$this->isEndResponse()){
            $cookie = new Cookie();
            $cookie->setName($name);
            $cookie->setValue($value);
            $cookie->setExpire($expire);
            $cookie->setPath($path);
            $cookie->setDomain($domain);
            $cookie->setSecure($secure);
            $cookie->setHttponly($httponly);
            $this->withAddedCookie($cookie);
            return true;
        }else{
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $file = $caller['file'];
            $line = $caller['line'];
            Trigger::error("response has end at status {$this->isEndResponse}",$file,$line);
            return false;
        }

    }

    function getSwooleResponse()
    {
        return $this->response;
    }


    function sendFile(string $sendFilePath)
    {
        $this->sendFile = $sendFilePath;
    }

    public function detach():?int
    {
        $fd = $this->response->fd;
        $this->isEndResponse = self::STATUS_RESPONSE_DETACH;
        $this->response->detach();
        return $fd;
    }

    static function createFromFd(int $fd):Response
    {
        $resp = \Swoole\Http\Response::create($fd);
        return new Response($resp);
    }

    final public function __toString():string
    {
        // TODO: Implement __toString() method.
        return Utility::toString($this);
    }
}