<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午8:44
 */

namespace Core\Http;
use Conf\Event;
use Core\Dispatcher;
use Core\Http\Message\Response as HttpResponse;
use Core\Http\Message\Status;
use Core\UrlParser;

class Response extends HttpResponse
{
    private static $instance;
    private $isEndResponse = 0;
    private $swoole_http_response = null;
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
    public function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null){
        if(!$this->isEndResponse()){
            //仅支持header重定向  不做meta定向
            $temp = " {$name}={$value};";
            if($expire != null){
                $temp .= " Expires=".date("D, d M Y H:i:s",$expire) . ' GMT;';
                $maxAge = $expire - time();
                $temp .=" Max-Age={$maxAge};";
            }
            if($path != null){
                $temp .= " Path={$path};";
            }
            if($domain != null){
                $temp .= " Domain={$domain};";
            }
            if($secure != null){
                $temp .=" Secure;";
            }
            if($httponly != null){
                $temp .=" HttpOnly;";
            }
            $this->withAddedHeader('Set-Cookie',$temp);
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

    function getSwooleResponse(){
        return $this->swoole_http_response;
    }
}