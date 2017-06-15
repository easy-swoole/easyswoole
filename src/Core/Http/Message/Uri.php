<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:32
 */

namespace Core\Http\Message;


class Uri
{
    private $host;
    private $userInfo;
    private $port = 80;
    private $path;
    private $query;
    private $fragment;
    private $scheme;
    function __construct($url = '')
    {
        if($url !== ''){
            $parts = parse_url($url);
            $this->scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
            $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
            $this->host = isset($parts['host']) ? $parts['host'] : '';
            $this->port = isset($parts['port']) ? $parts['port'] : 80;
            $this->path = isset($parts['path']) ? $parts['path'] : '';
            $this->query = isset($parts['query']) ? $parts['query'] : '';
            $this->fragment = isset($parts['fragment']) ? $parts['fragment'] : '';
            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
        }
    }

    public function getScheme()
    {
        // TODO: Implement getScheme() method.
        return $this->scheme;
    }

    public function getAuthority()
    {
        // TODO: Implement getAuthority() method.
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo()
    {
        // TODO: Implement getUserInfo() method.
        return $this->userInfo;
    }

    public function getHost()
    {
        // TODO: Implement getHost() method.
        return $this->host;
    }

    public function getPort()
    {
        // TODO: Implement getPort() method.
        return $this->port;
    }

    public function getPath()
    {
        // TODO: Implement getPath() method.
        return $this->path;
    }

    public function getQuery()
    {
        // TODO: Implement getQuery() method.
        return $this->query;
    }

    public function getFragment()
    {
        // TODO: Implement getFragment() method.
        return $this->fragment;
    }

    public function withScheme($scheme)
    {
        // TODO: Implement withScheme() method.
        if ($this->scheme === $scheme) {
            return $this;
        }
        $this->scheme = $scheme;
        return $this;
    }

    public function withUserInfo($user, $password = null)
    {
        // TODO: Implement withUserInfo() method.
        $info = $user;
        if ($password != '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }
        $this->userInfo = $info;
        return $this;
    }

    public function withHost($host)
    {
        // TODO: Implement withHost() method.
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }
        $this->host = $host;
        return $this;
    }

    public function withPort($port)
    {
        // TODO: Implement withPort() method.
        if ($this->port === $port) {
            return $this;
        }
        $this->port = $port;
        return $this;
    }

    public function withPath($path)
    {
        // TODO: Implement withPath() method.
        if ($this->path === $path) {
            return $this;
        }
        $this->path = $path;
        return $this;
    }

    public function withQuery($query)
    {
        // TODO: Implement withQuery() method.
        if ($this->query === $query) {
            return $this;
        }
        $this->query = $query;
        return $this;
    }

    public function withFragment($fragment)
    {
        // TODO: Implement withFragment() method.
        if ($this->fragment === $fragment) {
            return $this;
        }
        $this->fragment = $fragment;
        return $this;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        $uri = '';
        // weak type checks to also accept null until we can add scalar type hints
        if ($this->scheme != '') {
            $uri .= $this->scheme . ':';
        }
        if ($this->getAuthority() != ''|| $this->scheme === 'file') {
            $uri .= '//' . $this->getAuthority();
        }
        $uri .= $this->path;
        if ($this->query != '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment != '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }
}