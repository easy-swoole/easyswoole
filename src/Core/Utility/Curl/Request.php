<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午11:07
 */

namespace EasySwoole\Core\Utility\Curl;



class Request
{
    private $curlOPt = [
        CURLOPT_CONNECTTIMEOUT=>3,
        CURLOPT_TIMEOUT=>10,
        CURLOPT_AUTOREFERER=>true,
        CURLOPT_USERAGENT=>"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)",
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
        CURLOPT_HEADER=>true,
    ];

    private $fields = [];
    private $cookies = [];

    function __construct(string $url = null)
    {
        if($url !== null){
            $this->setUrl($url);
        }
    }

    public function setUrl(string $url):Request
    {
        $this->curlOPt[CURLOPT_URL] = $url;
        return $this;
    }

    public function addCookie(Cookie $cookie):Request
    {
        $this->cookies[$cookie->getName()] = $cookie->getValue();
        return $this;
    }

    public function addPost(Field $field,$isFile = false):Request
    {
        $this->fields['post'][$field->getName()] = $isFile ? new \CURLFile($field->getVal()) : $field->getVal();
        return $this;
    }

    public function addGet(Field $field):Request
    {
        $this->fields['get'][$field->getName()] = $field->getVal();
        return $this;
    }

    public function setUserOpt(array $opt,$isMerge = true):Request
    {
        if($isMerge){
            $this->curlOPt = $opt+ $this->curlOPt;
        }else{
            $this->curlOPt = $opt;
        }
        return $this;
    }

    public function exec():Response
    {
        $curl = curl_init();
        curl_setopt_array($curl,$this->getOpt());
        $result = curl_exec($curl);
        return new Response($result,$curl);
    }

    public function getOpt():array
    {
        $opt = $this->curlOPt;
        if(!empty($this->cookies)){
            $str = '';
            foreach ($this->cookies as $name=>$value){
                $str .= "{$name}={$value};";
            }
            $opt[CURLOPT_COOKIE] = $str;
        }
        if(isset($this->fields['get'])){
            $query = http_build_query($this->fields['get']);
            $opt[CURLOPT_URL] = rtrim( $opt[CURLOPT_URL],'?').'?'.$query;
        }
        if(isset($this->fields['post'])){
            //若用户已经设置了POST  则opt中的优先级最高
            if(!isset($opt[CURLOPT_POSTFIELDS])){
	            $opt[CURLOPT_POST] = true;
	            $opt[CURLOPT_POSTFIELDS] = $this->fields['post'];
            }
        }
        if(!empty($this->cookies))
        {
            //优先级同上
            if(!isset($opt[CURLOPT_COOKIE])){
                $str = '';
                foreach ($this->cookies as $cookie => $value){
                    $str .= $cookie.'='.$value.';';
                }
                $opt[CURLOPT_COOKIE] = $str;
            }
        }
        return $opt;
    }
}