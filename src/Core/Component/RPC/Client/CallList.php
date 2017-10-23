<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午5:38
 */

namespace Core\Component\RPC\Client;


use Core\Component\RPC\Common\Package;

class CallList
{
    private $taskList = [];
    function addCall($serverName,$action,array $args = null,callable $successCall = null,callable $failCall = null){
        $package = new Package();
        $package->setServerName($serverName);
        $package->setAction($action);
        $package->setArgs($args);
        $this->taskList[] = new Call($package,$successCall,$failCall);
        return $this;
    }

    function getTaskList(){
        return $this->taskList;
    }
}