<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午6:25
 */

namespace Core\Component\RPC\Client;


use Core\Component\RPC\Common\Config;
use Core\Component\RPC\Common\Package;

class TaskList
{
    protected $conf;
    protected $taskList = [];
    function __construct(Config $config)
    {
        $this->conf = $config;
    }

    function addCall($serverName,$action,array $args = null,callable $successCall = null,callable $failCall = null){
        $package = new Package();
        $package->setServerName($serverName);
        $package->setAction($action);
        $package->setArgs($args);
        $this->taskList[] = new TaskObj($package,$successCall,$failCall);
        return $this;
    }

    function getTaskList(){
        return $this->taskList;
    }

    function getConfig(){
        return $this->conf;
    }
}