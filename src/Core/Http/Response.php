<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;
use  EasySwoole\Core\Http\Message\Response as MessageResponse;


class Response extends MessageResponse
{
    private $response;
    const STATUS_NOT_END = 0;
    const STATUS_LOGICAL_END = 1;
    const STATUS_REAL_END = 2;
    private $isEndResponse = 0;//1 逻辑end  2真实end
    final public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
    }

    function end($realEnd = false){
//        $this->getH
        if($this->isEndResponse == self::STATUS_NOT_END){
            $this->isEndResponse = self::STATUS_LOGICAL_END;
        }
        if($realEnd === true && $this->isEndResponse !== self::STATUS_REAL_END){
            $this->isEndResponse = self::STATUS_REAL_END;
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
                $this->response->cookie($cookie->getName(),$cookie->getValue(),$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$cookie->getSecure(),$cookie->getHttponly());
            }
            $write = $this->getBody()->__toString();
            if(!empty($write)){
                $this->swoole_http_response->write($write);
            }
            $this->getBody()->close();
            $this->swoole_http_response->end();
        }
    }

    function isEndResponse(){
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
    function forward($pathTo,array $attribute = array()){
        $pathTo = UrlParser::pathInfo($pathTo);
        if(!$this->isEndResponse()){
            if($pathTo == UrlParser::pathInfo()){
                trigger_error("you can not forward a request in the same path : {$pathTo}");
            }else{
                $request = Request::getInstance();
                $request->getUri()->withPath($pathTo);
                $response = Response::getInstance();
                foreach ($attribute as $key => $value){
                    $request->withAttribute($key,$value);
                }
                Event::getInstance()->onRequest($request,$response);
                Dispatcher::getInstance()->dispatch();
            }
        }else{
            trigger_error("response has end");
        }
    }

    function getSwooleResponse()
    {
        return $this->response;
    }
}