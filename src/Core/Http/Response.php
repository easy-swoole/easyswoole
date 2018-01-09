<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;
use  EasySwoole\Core\Http\Message\Response as MessageResponse;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Message\Utility;
use EasySwoole\Core\Utility\Curl\Cookie;


class Response extends MessageResponse
{
    private $response;
    const STATUS_NOT_END = 0;
    const STATUS_LOGICAL_END = 1;
    const STATUS_REAL_END = 2;
    private $isEndResponse = 0;//1 逻辑end  2真实end

    private $autoEnd = false;
    private $autoResponse = true;

    final public function autoEnd(bool $bool = null):bool
    {
        if($bool !== null){
            $this->autoEnd = $bool;
        }
        return $this->autoEnd;
    }

    final public function autoResponse(bool $bool = null):bool
    {
        if($bool !== null){
            $this->autoResponse = $bool;
        }
        return $this->autoResponse;
    }

    final public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
        parent::__construct();
    }

    function end($realEnd = false){
        if($this->isEndResponse == self::STATUS_NOT_END){
            $this->isEndResponse = self::STATUS_LOGICAL_END;
        }
        if($realEnd === true && $this->isEndResponse !== self::STATUS_REAL_END){
            $this->isEndResponse = self::STATUS_REAL_END;
            $this->response->end();
        }
    }

    function response():bool
    {
        if(!$this->isEndResponse()){
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
            if(!empty($write)){
                $this->response->write($write);
            }
            $this->getBody()->close();
            return true;
        }else{
            return false;
        }
    }

    function isEndResponse()
    {
        return $this->isEndResponse;
    }

    function write($obj){
        if(!$this->isEndResponse()){
            if(is_object($obj)){
                if(method_exists($obj,"__toString")){
                    $obj = $obj->__toString();
                }else if(method_exists($obj,'jsonSerialize')){
                    $obj = json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                }else{
                    $obj = var_export($obj,true);
                }
            }else if(is_array($obj)){
                $obj = json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }
            $this->getBody()->write($obj);
            return true;
        }else{
            trigger_error("response has end");
            return false;
        }
    }

    function writeJson($statusCode = 200,$result = null,$msg = null){
        if(!$this->isEndResponse()){
            $data = Array(
                "code"=>$statusCode,
                "result"=>$result,
                "msg"=>$msg
            );
            $this->getBody()->write(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $this->withHeader('Content-type','application/json;charset=utf-8');
            $this->withStatus($statusCode);
            return true;
        }else{
            trigger_error("response has end");
            return false;
        }
    }

    function redirect($url,$status = Status::CODE_MOVED_TEMPORARILY){
        if(!$this->isEndResponse()){
            //仅支持header重定向  不做meta定向
            $this->withStatus($status);
            $this->withHeader('Location',$url);
        }else{
            trigger_error("response has end");
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
            trigger_error("response has end");
            return false;
        }

    }

    function getSwooleResponse()
    {
        return $this->response;
    }

    final public function __toString():string
    {
        // TODO: Implement __toString() method.
        return Utility::toString($this);
    }
}