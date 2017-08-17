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
    protected $body = '';
    protected $error;
    protected $errorNo;
    protected $curlInfo;
    protected $headerLine;
    protected $cookies = array();

    function __construct($rawResponse,$curlResource)
    {
        $this->curlInfo = curl_getinfo($curlResource);
        $this->error = curl_error($curlResource);
        $this->errorNo = curl_errno($curlResource);
        //处理头部信息
        $this->headerLine = substr($rawResponse, 0, $this->curlInfo['header_size']);
        $this->body = substr($rawResponse, $this->curlInfo['header_size']);
        //处理头部中的cookie
        preg_match_all("/Set-Cookie:(.*)\n/U",$this->headerLine,$ret);
        if(!empty($ret[0])){
            foreach($ret[0] as $item) {
                $item = explode(";",$item)[0];
                $item = ltrim($item,"Set-Cookie: ");
                $item = explode("=",$item);
                $this->cookies[$item[0]] = rtrim($item[1]);
            }
        }
        curl_close($curlResource);
    }

    /**
     * @return bool|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return int
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
        return $this->curlInfo;
    }

    /**
     * @return bool|string
     */
    public function getHeaderLine()
    {
        return $this->headerLine;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    public function getCookie($cookieName){
        return isset($this->cookies[$cookieName]) ? $this->cookies[$cookieName] : null;
    }
    function __toString()
    {
        // TODO: Implement __toString() method.
        $ret = '';
        if(!empty($this->headerLine)){
            $ret =  $this->headerLine."\n\r\n\r";
        }
        return $ret.$this->body;
    }


}