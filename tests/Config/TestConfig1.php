<?php
namespace EasySwoole\EasySwoole\Test\Config;
use EasySwoole\Config\AbstractConfig;

class TestConfig1 extends AbstractConfig {

    private $config = [
        'name' => 'easyswoole',
        'action' => 'getConf'
    ];

    function getConf($key = null)
    {
        // TODO: Implement getConf() method.
        return $this->config;
    }

    function setConf($key, $val): bool
    {
        // TODO: Implement setConf() method.
        $this->config[$key] = $val;
        return true;
    }

    function load(array $array): bool
    {
        // TODO: Implement load() method.
        $this->config = $array;
        return true;
    }

    function merge(array $array): bool
    {
        // TODO: Implement merge() method.
        $this->config = array_merge($this->config, $array);
        return true;
    }

    function clear(): bool
    {
        // TODO: Implement clear() method.
        $this->config = [];
        return true;
    }
}