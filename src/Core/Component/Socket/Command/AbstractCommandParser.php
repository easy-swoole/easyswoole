<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/10
 * Time: 下午11:18
 */

namespace Core\Component\Socket\Command;


use Core\Component\Socket\Client\Client;
use Core\Component\Socket\Type;

abstract class AbstractCommandParser
{
    private $command;
    abstract protected function handler($data);
    function parse(Client $client,$data){
        $this->command = new Command();
        $this->command->setRawData($data);
        $this->command->setClient($client);
        $this->handler($data);
        return $this->getCommand();
    }

    function getCommand(){
        //为了ide
        if($this->command instanceof Command){
            return $this->command;
        }
    }

}