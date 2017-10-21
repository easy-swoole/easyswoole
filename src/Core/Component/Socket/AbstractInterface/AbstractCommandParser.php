<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午5:49
 */

namespace Core\Component\Socket\AbstractInterface;



use Core\Component\RPC\Client\Client;
use Core\Component\Socket\Common\Command;

abstract class AbstractCommandParser
{
    private $command;
    abstract protected function handler(Command $result,AbstractClient $client,$data);

    function parser(AbstractClient $client,$data){
        $this->command = new Command();
        $this->handler($this->command,$client,$data);
        return $this;
    }

    function getResultCommand(){
        //为了IDE提示
        if(!$this->command instanceof Command){
            $this->command = new Command();
        }
        return $this->command;
    }
}