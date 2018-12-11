<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 3:12 PM
 */

namespace EasySwoole\EasySwoole\Actor;


class ActorClient
{
    protected $conf;
    function __construct(ActorConfig $conf)
    {
        $this->conf = $conf;
    }
}