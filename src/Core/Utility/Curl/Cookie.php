<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午11:05
 */

namespace EasySwoole\Core\Utility\Curl;


class Cookie
{
    private $name;
    private $value;
    private $expire = 0;
    private $path = '/';
    private $domain = null;
    private $secure = false;
    private $httpOnly = false;

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
     * @return int
     */
    public function getExpire(): ?int
    {
        return $this->expire;
    }

    /**
     * @param int $expire
     */
    public function setExpire(?int $expire)
    {
        $this->expire = $expire;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param bool $httpOnly
     */
    public function setHttpOnly(bool $httpOnly)
    {
        $this->httpOnly = $httpOnly;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return "{$this->name}={$this->value};";
    }
}