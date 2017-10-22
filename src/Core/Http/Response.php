<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午8:44
 */

namespace Core\Http;
use Conf\Event;
use Core\Http\Message\Response as HttpResponse;
use Core\Http\Message\Status;
use Core\Http\Session\Response as SessionResponse;
use Core\Http\Session\Session;
use Core\Utility\Curl\Cookie;

class Response extends HttpResponse
{
    private static $instance;
    private $isEndResponse = 0;
    private $swoole_http_response = null;
    private $session = null;
    static function getInstance(\swoole_http_response $response = null){
        if($response !== null){
            self::$instance = new Response($response);
        }
        return self::$instance;
    }
    function __construct(\swoole_http_response $response)
    {
        $this->swoole_http_response = $response;
    }
    function end(){
        if(!$this->isEndResponse){
            Session::getInstance()->close();
            $this->isEndResponse = 1;
            return true;
        }else{
            return false;
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
            $this->getBody()->rewind();
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
    function redirect($url){
        if(!$this->isEndResponse()){
            //仅支持header重定向  不做meta定向
            $this->withStatus(Status::CODE_MOVED_TEMPORARILY);
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

    function session(){
        if(!isset($this->session)){
            $this->session = new SessionResponse();
        }
        return $this->session;
    }

    function getSwooleResponse(){
        return $this->swoole_http_response;
    }
}