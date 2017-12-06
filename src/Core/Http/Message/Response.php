<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/5
 * Time: 下午12:03
 */

namespace EasySwoole\Core\Http\Message;


use EasySwoole\Core\Utility\Curl\Cookie;

class Response extends Message
{
    private $statusCode = 200;
    private $reasonPhrase = 'OK';
    private $cookies = [];
    public function getStatusCode()
    {
        // TODO: Implement getStatusCode() method.
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        // TODO: Implement withStatus() method.
        if($code === $this->statusCode){
            return $this;
        }else{
            $this->statusCode = $code;
            if(empty($reasonPhrase)){
                $this->reasonPhrase = Status::getReasonPhrase($this->statusCode);
            }else{
                $this->reasonPhrase = $reasonPhrase;
            }
            return $this;
        }
    }

    public function getReasonPhrase()
    {
        // TODO: Implement getReasonPhrase() method.
        return $this->reasonPhrase;
    }

    function withAddedCookie(Cookie $cookie){
        $this->cookies[] = $cookie;
        return $this;
    }

    function getCookies(){
        return $this->cookies;
    }
}