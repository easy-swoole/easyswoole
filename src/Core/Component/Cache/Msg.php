<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/23
 * Time: 下午8:38
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Core\Component\Spl\SplBean;
use EasySwoole\Core\Utility\Random;

class Msg extends SplBean
{
    protected $command = '';
    protected $token;
    protected $data = '';
    protected $args = [];
    protected $time;

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        if($this->getArg('__isUnPack')){
            return $this->data;
        }
        $this->setArg('__isUnPack',true);
        if($this->getArg('__isCache')){
            $this->data = \swoole_serialize::unpack(Utility::readFile($this->data));
        }else{
            $this->data = \swoole_serialize::unpack($this->data);
        }
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->setArg('__isUnPack',false);
        $this->setArg('__isCache',false);
        $data = \swoole_serialize::pack($data);
        $len = strlen($data);
        if($len > 8*1024){
            $this->data = Utility::writeFile($data);
            $this->setArg('__isCache',true);
        }else{
            $this->data = $data;
        }
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    public function setArg($key,$item)
    {
        $this->args[$key] = $item;
    }

    protected function initialize(): void
    {
        if(empty($this->time)){
            $this->time = microtime(true);
        }
    }

}