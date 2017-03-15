<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午11:17
 */

namespace Core\Utility\Curl;


class Response
{
    protected $body;
    protected $error;
    protected $errorNo;
    protected $info;
    protected $rawResponse;

    function __construct($rawResponse,$curlInfo,$error,$errorNo)
    {
        $this->rawResponse = $rawResponse;
        $this->info = $curlInfo;
        $this->error = $error;
        $this->errorNo = $errorNo;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return Utility::trimHeader($this->rawResponse);
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return Utility::getHeader($this->rawResponse);
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getErrorNo()
    {
        return $this->errorNo;
    }

    /**
     * @return mixed
     */
    public function getCurlInfo()
    {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    function getCookie($toString = false){
        return Utility::getCookieFromHeader($this->rawResponse,$toString);
    }
    function __toString()
    {
        // TODO: Implement __toString() method.
        if(!isset($this->body)){
            $this->body = $this->getBody();
        }
        return $this->body;
    }

}