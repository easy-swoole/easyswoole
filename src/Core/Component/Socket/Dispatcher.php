<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/9
 * Time: 下午10:19
 */

namespace Core\Component\Socket;


use Core\Component\Socket\Command\AbstractCommandParser;

class Dispatcher
{
    private $map = [];
    private $defaultHandler = null;
    private $commandParser = null;
    public function registerCommand($command,callable $handler){
        $this->map[$command] = $handler;
    }

    function setCommandParser(AbstractCommandParser $parser){
        $this->commandParser = $parser;
    }

    function setDefaultHandler(callable $handler){
        $this->defaultHandler = $handler;
    }

    function dispatch($client,$data){
        if($this->commandParser instanceof AbstractCommandParser){
            $command = $this->commandParser->parse($client,$data);
            $handler = null;
            if(isset($this->map[$command->getCommand()])){
                $handler = $this->map[$command->getCommand()];
            }else{
                $handler = $this->defaultHandler;
            }
            if(is_callable($handler)){
                call_user_func($handler,$command);
            }
        }else{
            trigger_error("command parser not set");
        }
    }
}