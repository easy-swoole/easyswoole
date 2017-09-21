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
use Core\Component\Sys\ErrorHandler;
use Core\Component\Spl\SplError;
use Core\Component\Sys\SysConst;
use Core\Swoole\Server;
use Core\Utility\File;

class Core
{
    protected static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function run(){
        Server::getInstance()->startServer();
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
        Event::getInstance()->frameInitialize();
        $this->sysDirectoryInit();
        $this->registerErrorHandler();
        Event::getInstance()->frameInitialized();
        return $this;
    }

    private function defineSysConst(){
        define("ROOT",realpath(__DIR__.'/../'));
    }
    private function sysDirectoryInit(){
        //创建临时目录
        $tempDir = Di::getInstance()->get(SysConst::TEMP_DIRECTORY);
        if(empty($tempDir)){
            $tempDir = ROOT."/Temp";
            Di::getInstance()->set(SysConst::TEMP_DIRECTORY,$tempDir);
        }
        if(!File::createDir($tempDir)){
            die("create Temp Directory:{$tempDir} fail");
        }
        //创建日志目录
        $logDir = Di::getInstance()->get(SysConst::LOG_DIRECTORY);
        if(empty($logDir)){
            $logDir = ROOT."/Log";
            Di::getInstance()->set(SysConst::LOG_DIRECTORY,$logDir);
        }
        //创建日志目录
        if(!File::createDir($logDir)){
            die("create log Directory:{$logDir} fail");
        }
    }

    private static function registerAutoLoader(){
        require_once __DIR__."/AutoLoader.php";
        $loader = AutoLoader::getInstance();
        $loader->addNamespace("App","App");
        $loader->addNamespace("Core","Core");
        $loader->addNamespace("Conf","Conf");
        //添加系统依赖组件
        $loader->addNamespace("FastRoute","Core/Vendor/FastRoute");
        $loader->addNamespace("SuperClosure","Core/Vendor/SuperClosure");
        $loader->addNamespace("PhpParser","Core/Vendor/PhpParser");
    }

    private function registerErrorHandler(){
        $conf = Config::getInstance()->getConf("DEBUG");
        if($conf['ENABLE'] == true){
            ini_set("display_errors", "On");
            error_reporting(E_ALL | E_STRICT);
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
}