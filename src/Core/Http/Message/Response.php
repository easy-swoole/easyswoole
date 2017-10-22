<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:28
 */

namespace Core\Http\Message;


use Core\Utility\Curl\Cookie;

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
        $this->cookies[$cookie->getName()] = $cookie;
        return $this;
    }

    function getCookies(){
        return $this->cookies;
    }
}