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
use Core\AbstractInterface\AbstractController;
use Core\AbstractInterface\AbstractShutdownHandler;
use Core\AbstractInterface\ExceptionHandlerInterface;
use Core\Component\Di;
use Core\Component\Error\ErrorHandler;
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
        $this->defineSysConst();
        $this->registerAutoLoader();
        Event::getInstance()->frameInitialize();
        $this->registerErrorHandler();
        $this->registerExceptionHandler();
        $this->registerShutDownHandler();
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
        //添加应用目录
        $loader->addNamespace("App","App");
    }

    private function registerErrorHandler(){
        $conf = Config::getInstance()->getConf("DEBUG");
        if($conf['ENABLE'] == true){
            $errorHandler = Di::getInstance()->get(SysConst::DI_ERROR_HANDLER);
            if($errorHandler instanceof AbstractController){
            }else{
                /*
                 * default handler
                 */
                $errorHandler = new ErrorHandler();
            }
            set_error_handler(array($errorHandler,'handlerRegister'));
        }
    }
    private function registerShutDownHandler(){
        $handler = Di::getInstance()->get(SysConst::DI_SHUTDOWN_HANDLER);
        if($handler instanceof AbstractShutdownHandler){
            register_shutdown_function(array(
                $handler,"handler"
            ));
        }
    }
    private function registerExceptionHandler(){
        $handler = Di::getInstance()->get(SysConst::DI_EXCEPTION_HANDLER);
        if($handler instanceof ExceptionHandlerInterface){
            set_exception_handler(array(
                $handler,"handler"
            ));
        }
    }
}