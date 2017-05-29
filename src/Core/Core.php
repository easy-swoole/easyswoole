<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:54
 */

namespace Core;


use Conf\Config;
use Conf\Event;
use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Component\Di;
use Core\Component\ErrorHandler;
use Core\Component\Spl\SplError;
use Core\Component\SysConst;
use Core\Swoole\SwooleHttpServer;

class Core
{
    protected static $instance;
    static function getInstance(callable $preHandler = null){
        if(!isset(self::$instance)){
            self::$instance = new static($preHandler);
        }
        return self::$instance;
    }
    function run(){
        SwooleHttpServer::getInstance()->startServer();
    }
    function __construct(callable $preHandler = null)
    {
        if(is_callable($preHandler)){
            call_user_func($preHandler);
        }
    }

    /*
     * initialize frameWork
     */
    function frameWorkInitialize(){
        if(phpversion() < 5.6){
            die("php version must >= 5.6");
        }
        $this->defineSysConst();
        $this->registerAutoLoader();
        $this->setDefaultAppDirectory();
        Event::getInstance()->frameInitialize();
        $this->registerErrorHandler();
        return $this;
    }

    private function defineSysConst(){
        define("ROOT",realpath(__DIR__.'/../'));
    }

    private static function registerAutoLoader(){
        require_once __DIR__."/AutoLoader.php";
        $loader = AutoLoader::getInstance();
        //添加系统核心目录
        $loader->addNamespace("Core","Core");
        //添加conf目录
        $loader->addNamespace("Conf","Conf");
        //添加系统依赖组件
        $loader->addNamespace("FastRoute","Core/Vendor/FastRoute");
        $loader->addNamespace("SuperClosure","Core/Vendor/SuperClosure");
        $loader->addNamespace("PhpParser","Core/Vendor/PhpParser");
    }

    private function registerErrorHandler(){
        $conf = Config::getInstance()->getConf("DEBUG");
        if($conf['ENABLE'] == true){
            set_error_handler(function($errorCode, $description, $file = null, $line = null, $context = null)use($conf){
                $error = new SplError();
                $error->setErrorCode($errorCode);
                $error->setDescription($description);
                $error->setFile($file);
                $error->setLine($line);
                $error->setContext($context);
                $errorHandler = Di::getInstance()->get(SysConst::DI_ERROR_HANDLER);
                if(!is_a($errorHandler,ErrorHandlerInterface::class)){
                    $errorHandler = new ErrorHandler();
                }
                $errorHandler->handler($error);
                if($conf['DISPLAY_ERROR'] == true){
                    $errorHandler->display($error);
                }
                if($conf['LOG'] == true){
                    $errorHandler->log($error);
                }
            });
        }
    }
    private function setDefaultAppDirectory(){
        $dir = Di::getInstance()->get(SysConst::APPLICATION_DIR);
        if(empty($dir)){
            $dir = "App";
            Di::getInstance()->set(SysConst::APPLICATION_DIR,$dir);
        }
        $prefix = $dir;
        AutoLoader::getInstance()->addNamespace($prefix,$dir);
    }
}