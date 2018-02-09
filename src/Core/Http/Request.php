<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;

use EasySwoole\Config;
use EasySwoole\Core\Http\Message\ServerRequest;
use EasySwoole\Core\Http\Message\Stream;
use EasySwoole\Core\Http\Message\UploadFile;
use EasySwoole\Core\Http\Message\Uri;
use EasySwoole\Core\Http\Message\Utility;


class Request  extends ServerRequest
{
    private $request;

    function __construct(\swoole_http_request $request)
    {
        $this->request = $request;
        $this->initHeaders();
        $protocol = str_replace('HTTP/', '', $request->server['server_protocol']) ;
        //为单元测试准备
        if($request->fd){
            $body = new Stream($request->rawContent());
        }else{
            $body = new Stream('');
        }
        $uri = $this->initUri();
        $files = $this->initFiles();
        $method = $request->server['request_method'];
        parent::__construct($method, $uri, null, $body, $protocol, $request->server);
        $this->withCookieParams($this->initCookie())->withQueryParams($this->initGet())->withParsedBody($this->initPost())->withUploadedFiles($files);
    }

    function getRequestParam(...$key)
    {
        $data = array_merge($this->getParsedBody(),$this->getQueryParams());;
        if(empty($key)){
            return $data;
        }else{
           $res = [];
           foreach ($key as $item){
               $res[$item] = isset($data[$item])? $data[$item] : null;
           }
           if(count($key) == 1){
               return array_shift($res);
           }else{
               return $res;
           }
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
        //host与port以header为准，防止经过proxy
        if(isset($this->request->header['host'])){
            $host = $this->request->header['host'];
            $host = explode(":",$host);
            $realHost = $host[0];
            $port = isset($host[1]) ? $host[1] : 80;
        }else{
            $realHost = '127.0.0.1';
            $port = $this->request->server['server_port'];
        }
        $uri->withHost($realHost);
        $uri->withPort($port);
        return $uri;
    }

    private function initHeaders()
    {
        $headers = isset($this->request->header) ? $this->request->header :[];
        foreach ($headers as $header => $val){
            $this->withAddedHeader($header,$val);
        }
    }

    private function initFiles()
    {
        if(isset($this->request->files)){
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
        return isset($this->request->cookie) ? $this->request->cookie : array();
    }

    private function initPost()
    {
        return isset($this->request->post) ? $this->request->post : array();
    }

    private function initGet()
    {
        return isset($this->request->get) ? $this->request->get : array();
    }

    final public function __toString():string
    {
        // TODO: Implement __toString() method.
        return Utility::toString($this);
    }
}