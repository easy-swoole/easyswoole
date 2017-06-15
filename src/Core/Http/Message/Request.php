<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:25
 */

namespace Core\Http\Message;


class Request extends Message
{
    private $uri;
    private $method;
    private $target;
    function __construct(
        $method = 'GET',Uri $uri = null,array $headers = null, Stream $body = null, $protocolVersion = '1.1'
    )
    {
        $this->method = $method;
        if($uri != null){
            $this->uri = $uri;
        }
        parent::__construct($headers, $body, $protocolVersion);
    }

    public function getRequestTarget()
    {
        // TODO: Implement getRequestTarget() method.
        if (!empty($this->target)) {
            return $this->target;
        }
        if($this->uri instanceof Uri){
            $target = $this->uri->getPath();
            if ($target == '') {
                $target = '/';
            }
            if ($this->uri->getQuery() != '') {
                $target .= '?' . $this->uri->getQuery();
            }
        }else{
            $target = "/";
        }
        return $target;
    }

    public function withRequestTarget($requestTarget)
    {
        // TODO: Implement withRequestTarget() method.
        $this->target = $requestTarget;
        return $this;
    }

    public function getMethod()
    {
        // TODO: Implement getMethod() method.
        return $this->method;
    }

    public function withMethod($method)
    {
        // TODO: Implement withMethod() method.
        $this->method = strtoupper($method);
        return $this;
    }

    public function getUri()
    {
        // TODO: Implement getUri() method.
        return $this->uri;
    }

    public function withUri(Uri $uri, $preserveHost = false)
    {
        // TODO: Implement withUri() method.
        if ($uri === $this->uri) {
            return $this;
        }
        $this->uri = $uri;
        if (!$preserveHost) {
            $host = $this->uri->getHost();
            if (!empty($host)) {
                if (($port = $this->uri->getPort()) !== null) {
                    $host .= ':' . $port;
                }
                if ($this->getHeader('host')) {
                    $header = $this->getHeader('host');
                } else {
                    $header = 'Host';
                }
                $this->withHeader($header,$host);
            }
        }
        return $this;
    }
}