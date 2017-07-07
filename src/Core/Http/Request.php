<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午8:05
 */

namespace Core\Http;


use Core\Http\Message\ServerRequest;
use Core\Http\Message\Stream;
use Core\Http\Message\UploadFile;
use Core\Http\Message\Uri;
use Core\Utility\Validate\Validate;

class Request extends ServerRequest
{
    private static $instance;
    private  $swoole_http_request = null;
    static function getInstance(\swoole_http_request $request = null){
        if($request !== null){
            self::$instance = new Request($request);
        }
        return self::$instance;
    }
    function __construct(\swoole_http_request $request)
    {
        $this->swoole_http_request = $request;
        $this->initHeaders();
        $protocol = str_replace('HTTP/', '', $this->swoole_http_request->server['server_protocol']) ;
        $body = new Stream($this->swoole_http_request->rawContent());
        $uri = $this->initUri();
        $files = $this->initFiles();
        $method = $this->swoole_http_request->server['request_method'];
        parent::__construct($method, $uri, null, $body, $protocol, $this->swoole_http_request->server);
        $this->withCookieParams($this->initCookie())->withQueryParams($this->initGet())->withParsedBody($this->initPost())->withUploadedFiles($files);
    }
    function getRequestParam($keyOrKeys = null){
        if($keyOrKeys !== null){
            if(is_string($keyOrKeys)){
                $ret = $this->getParsedBody($keyOrKeys);
                if(empty($ret)){
                    $ret = $this->getQueryParam($keyOrKeys);
                }
                return $ret;
            }else if(is_array($keyOrKeys)){
                $data = $this->getRequestParam();
                return array_intersect_key($data, array_flip($keyOrKeys));
            }else{
                return null;
            }
        }else{
            return array_merge($this->getParsedBody(),$this->getQueryParams());
        }
    }
    function requestParamsValidate(Validate $validate){
        return $validate->validate($this->getRequestParam());
    }
    function getSwooleRequest(){
        return $this->swoole_http_request;
    }
    private function initUri(){
        $uri = new Uri();
        $uri->withScheme("http");
        $uri->withPath($this->swoole_http_request->server['path_info']);
        $query = isset($this->swoole_http_request->server['query_string']) ? $this->swoole_http_request->server['query_string'] : '';
        $uri->withQuery($query);
        $host = $this->swoole_http_request->header['host'];
        $host = explode(":",$host);
        $uri->withHost($host[0]);
        $uri->withPort($host[1]);
        return $uri;
    }
    private function initHeaders(){
        $headers = $this->swoole_http_request->header;
        foreach ($headers as $header => $val){
            $this->withAddedHeader($header,$val);
        }
    }
    private function initFiles(){
        if(isset($this->swoole_http_request->files)){
            $normalized = array();
            foreach ($this->swoole_http_request->files as $key => $value) {
                $normalized[$key] = new UploadFile(
                    $value['tmp_name'],
                    (int) $value['size'],
                    (int) $value['error'],
                    $value['name'],
                    $value['type']
                );
            }
            return $normalized;
        }else{
            return array();
        }
    }
    private function initCookie(){
        return isset($this->swoole_http_request->cookie) ? $this->swoole_http_request->cookie : array();
    }
    private function initPost(){
        return isset($this->swoole_http_request->post) ? $this->swoole_http_request->post : array();
    }
    private function initGet(){
        return isset($this->swoole_http_request->get) ? $this->swoole_http_request->get : array();
    }
}