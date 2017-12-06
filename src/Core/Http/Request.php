<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;

use EasySwoole\Core\Http\Message\ServerRequest;
use EasySwoole\Core\Http\Message\Stream;
use EasySwoole\Core\Http\Message\UploadFile;
use EasySwoole\Core\Http\Message\Uri;
use EasySwoole\Core\Http\Message\Utility;

class Request  extends ServerRequest
{
    private $request;

    final function __construct(\swoole_http_request $request)
    {
        $this->request = $request;
        $this->initHeaders();
        $protocol = str_replace('HTTP/', '', $request->server['server_protocol']) ;
        $body = new Stream($request->rawContent());
        $uri = $this->initUri();
        $files = $this->initFiles();
        $method = $request->server['request_method'];
        parent::__construct($method, $uri, null, $body, $protocol, $request->server);
        $this->withCookieParams($this->initCookie())->withQueryParams($this->initGet())->withParsedBody($this->initPost())->withUploadedFiles($files);
    }

    function getRequestParam($keyOrKeys = null, $default = null)
    {
        if($keyOrKeys !== null){
            if(is_string($keyOrKeys)){
                $ret = $this->getParsedBody($keyOrKeys);
                if($ret === null){
                    $ret = $this->getQueryParam($keyOrKeys);
                    if ($ret === null){
                        if ($default !== null){
                            $ret = $default;
                        }
                    }
                }
                return $ret;
            }else if(is_array($keyOrKeys)){
                if (!is_array($default)){
                    $default = array();
                }
                $data = $this->getRequestParam();
                $keysNull = array_fill_keys(array_values($keyOrKeys), null);
                if($keysNull === null){
                    $keysNull = [];
                }
                $all =  array_merge($keysNull, $default, $data);
                $all = array_intersect_key($all, $keysNull);
                return $all;
            }else{
                return null;
            }
        }else{
            return array_merge($this->getParsedBody(),$this->getQueryParams());
        }
    }


    function getSwooleRequest()
    {
        return $this->request;
    }

    private function initUri()
    {
        $uri = new Uri();
        $uri->withScheme("http");
        $uri->withPath($this->request->server['path_info']);
        $query = isset($this->request->server['query_string']) ? $this->request->server['query_string'] : '';
        $uri->withQuery($query);
        $host = $this->request->header['host'];
        $host = explode(":",$host);
        $uri->withHost($host[0]);
        $port = isset($host[1]) ? $host[1] : 80;
        $uri->withPort($port);
        return $uri;
    }

    private function initHeaders()
    {
        $headers = $this->request->header;
        foreach ($headers as $header => $val){
            $this->withAddedHeader($header,Utility::headerItemToArray($val));
        }
    }

    private function initFiles()
    {
        if(isset($this->swoole_http_request->files)){
            $normalized = array();
            foreach ($this->request->files as $key => $value) {
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

    private function initCookie()
    {
        return isset($this->swoole_http_request->cookie) ? $this->request->cookie : array();
    }

    private function initPost()
    {
        return isset($this->swoole_http_request->post) ? $this->request->post : array();
    }

    private function initGet()
    {
        return isset($this->swoole_http_request->get) ? $this->request->get : array();
    }

    final public function __toString():string
    {
        // TODO: Implement __toString() method.
        return Utility::toString($this);
    }

}