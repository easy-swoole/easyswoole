<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/12
 * Time: 下午12:53
 */
namespace EasySwoole\Core\Swoole\Coroutine\Client;
use \Swoole\Coroutine\Http\Client;

class Http
{
    protected $port = 80;
    protected $ssl = false;
    protected $host;
    protected $queryPath = '/';
    protected $get = [];
    protected $post = [];
    protected $file = [];
    protected $header = [];
    protected $setting = [];
    function __construct(?string $url = null)
    {
        if(!empty($url)){
            $this->parserUrl($url);
        }
    }
    function setUrl(string $url)
    {
        $this->parserUrl($url);
        return $this;
    }
    function set(array $set)
    {
        $this->setting = $set;
        return $this;
    }
    function setHeader(array $data)
    {
        $this->header = $data;
        return $this;
    }
    function setGet(array $data)
    {
        $this->get = $data;
        return $this;
    }
    function setPost(array $data)
    {
        $this->post = $data;
        return $this;
    }
    function addFile($filePath,$fileName)
    {
        $this->file[$fileName] = $filePath;
        return $this;
    }
    protected function parserUrl(string $url)
    {
        $data = parse_url($url);
        if($data['scheme'] == 'https'){
            $this->ssl = true;
            $this->port = 443;
        }
        if(isset($data['host'])){
            $this->host = $data['host'];
        }
        if(isset($data['port'])){
            $this->port = $data['port'];
        }
        if(isset($data['path'])){
            $this->queryPath = $data['path'];
        }
        if(isset($data['query'])){
            parse_str($data['query'], $this->get);
        }
    }
    protected function reset()
    {
        $this->port = 80;
        $this->ssl = false;
        $this->host;
        $this->queryPath = '/';
        $this->get = [];
        $this->post = [];
        $this->file = [];
        $this->header = [];
    }
    /*
     * 延迟收包请注意close
     */
    function exec($setDefer = false,$autoRest = true)
    {
        $client = new Client($this->host, $this->port,$this->ssl);
        if(!empty($this->setting)){
            $client->set($this->setting);
        }
        if(!empty($this->header)){
            $client->setHeaders($this->header);
        }
        if(!empty($this->get)){
            $this->queryPath = $this->queryPath.'?'.http_build_query($this->get);
        }
        if(!empty($this->file)){
            foreach ($this->file as $name => $path){
                $client->addFile($path, $name);
            }
        }
        if($setDefer){
            $client->setDefer();
        }
        if(!empty($this->file) || !empty($this->post)){
            $client->post($this->queryPath,$this->post);
        }else{
            $client->get($this->queryPath);
        }
        if($autoRest){
            $this->reset();
        }
        if(!$setDefer){
            $client->close();
            return (array)$client;
        }else{
            return $client;
        }
    }
}