<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午1:44
 */

namespace Core\Http\Message;


class ServerRequest extends Request
{
    private $attributes = [];
    private $cookieParams = [];
    private $parsedBody;
    private $queryParams = [];
    private $serverParams;
    private $uploadedFiles = [];
    function __construct(
        $method = 'GET', Uri $uri = null, array $headers = null, Stream $body = null, $protocolVersion = '1.1',$serverParams = array()
    )
    {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    public function getServerParams()
    {
        // TODO: Implement getServerParams() method.
        return $this->serverParams;
    }

    public function getCookieParams($name = null)
    {
        // TODO: Implement getCookieParams() method.
        if($name === null){
            return $this->cookieParams;
        }else{
            if(isset($this->cookieParams[$name])){
                return $this->cookieParams[$name];
            }else{
                return null;
            }
        }

    }

    public function withCookieParams(array $cookies)
    {
        // TODO: Implement withCookieParams() method.
        $this->cookieParams = $cookies;
        return $this;
    }

    public function getQueryParams()
    {
        // TODO: Implement getQueryParams() method.
        return $this->queryParams;
    }

    public function getQueryParam($name){
        $data = $this->getQueryParams();
        if(isset($data[$name])){
            return $data[$name];
        }else{
            return null;
        }
    }

    public function withQueryParams(array $query)
    {
        // TODO: Implement withQueryParams() method.
        $this->queryParams = $query;
        return $this;
    }

    public function getUploadedFiles()
    {
        // TODO: Implement getUploadedFiles() method.
        return $this->uploadedFiles;
    }

    public function getUploadedFile($name)
    {
        // TODO: Implement getUploadedFiles() method.
        if(isset($this->uploadedFiles[$name])){
            return $this->uploadedFiles[$name];
        }else{
            return null;
        }
    }

    /**
     * @param array $uploadedFiles must be array of UploadFile Instance
     * @return ServerRequest
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        // TODO: Implement withUploadedFiles() method.
        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    public function getParsedBody($name = null)
    {
        // TODO: Implement getParsedBody() method.
        if($name !== null){
            if(isset($this->parsedBody[$name])){
                return $this->parsedBody[$name];
            }else{
                return null;
            }
        }else{
            return $this->parsedBody;
        }
    }

    public function withParsedBody($data)
    {
        // TODO: Implement withParsedBody() method.
        $this->parsedBody = $data;
        return $this;
    }

    public function getAttributes()
    {
        // TODO: Implement getAttributes() method.
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        // TODO: Implement getAttribute() method.
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$name];
    }

    public function withAttribute($name, $value)
    {
        // TODO: Implement withAttribute() method.
        $this->attributes[$name] = $value;
        return $this;
    }

    public function withoutAttribute($name)
    {
        // TODO: Implement withoutAttribute() method.
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }
        unset($this->attributes[$name]);
        return $this;
    }
}