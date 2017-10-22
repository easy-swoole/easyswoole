<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/22
 * Time: 下午10:50
 */

namespace Core\Utility\Curl;


class Cookie
{
    private $name;
    private $value;
    private $expire = 0;
    private $path = '/';
    private $domain = '';
    private $secure = false;
    private $httponly = false;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param mixed $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param mixed $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return mixed
     */
    public function getHttponly()
    {
        return $this->httponly;
    }

    /**
     * @param mixed $httponly
     */
    public function setHttponly($httponly)
    {
        $this->httponly = $httponly;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return "{$this->name}={$this->value};";
    }

}