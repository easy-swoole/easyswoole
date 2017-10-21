<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午5:40
 */

namespace Core\Component\Socket\Common;


class CommandList
{
    protected $list = [];
    function addCommandHandler($commandStr,callable $callback){
        $this->list[$commandStr] = $callback;
    }

    function setDefaultHandler(callable $callback){
        $this->list['DEFAULT_HANDLER'] = $callback;
    }

    function getHandler(Command $command){
        $name = $command->getCommand();
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else if(isset($this->list['DEFAULT_HANDLER'])){
            return $this->list['DEFAULT_HANDLER'];
        }else{
            return null;
        }
    }
}