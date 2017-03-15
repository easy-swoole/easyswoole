<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/5
 * Time: 下午7:41
 */

namespace Core\AbstractInterface;


use Conf\Config;
use Core\Component\Error\Error;

abstract class AbstractErrorHandler
{
    protected $error;
    private $conf;
    function __construct()
    {
        $this->conf = Config::getInstance()->getConf("DEBUG");
    }

    /**
     * @param int $errorCode 错误代码
     * @param string $description 错误描述
     * @param null $file
     * @param null $line
     * @param null $context
     */
    function handlerRegister($errorCode, $description, $file = null, $line = null, $context = null){
        if (error_reporting() !== 0) {
            $error = new Error($errorCode, $description, $file, $line, $context);
            $this->error = $error;
            if($this->conf['ENABLE'] == true){
                $this->handler($error);
                if($this->conf['DISPLAY_ERROR'] == true){
                    $this->display($error);
                }
                if($this->conf['LOG'] == true){
                    $this->log($error);
                }
            }
        }
    }
    abstract function handler(Error $error);
    abstract function display(Error $error);
    abstract function log(Error $error);
}