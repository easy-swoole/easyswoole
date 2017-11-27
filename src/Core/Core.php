<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:54
 */

//============================当你妹的程序员啊======================
//		                               .. .vr
//		                             qBMBBBMBMY
//		                            8BBBBBOBMBMv
//		                          iMBMM5vOY:BMBBv
//		          .r,             OBM;   .: rBBBBBY
//		          vUL             7BB   .;7. LBMMBBM.
//		         .@Wwz.           :uvir .i:.iLMOMOBM..
//		          vv::r;             iY. ...rv,@arqiao.
//		           Li. i:             v:.::::7vOBBMBL..
//		           ,i7: vSUi,         :M7.:.,:u08OP. .
//		             .N2k5u1ju7,..     BMGiiL7   ,i,i.
//		              :rLjFYjvjLY7r::.  ;v  vr... rE8q;.:,,
//		             751jSLXPFu5uU@guohezou.,1vjY2E8@Yizero.
//		             BB:FMu rkM8Eq0PFjF15FZ0Xu15F25uuLuu25Gi.
//		           ivSvvXL    :v58ZOGZXF2UUkFSFkU1u125uUJUUZ,
//		         :@kevensun.      ,iY20GOXSUXkSuS2F5XXkUX5SEv.
//		     .:i0BMBMBBOOBMUi;,        ,;8PkFP5NkPXkFqPEqqkZu.
//		   .rqMqBBMOMMBMBBBM .           @kexianli.S11kFSU5q5
//		 .7BBOi1L1MM8BBBOMBB..,          8kqS52XkkU1Uqkk1kUEJ
//		 .;MBZ;iiMBMBMMOBBBu ,           1OkS1F1X5kPP112F51kU
//		   .rPY  OMBMBBBMBB2 ,.          rME5SSSFk1XPqFNkSUPZ,.
//		          ;;JuBML::r:.:.,,        SZPX0SXSP5kXGNP15UBr.
//		              L,    :@huhao.      :MNZqNXqSqXk2E0PSXPE .
//		          viLBX.,,v8Bj. i:r7:,     2Zkqq0XXSNN0NOXXSXOU
//		        :r2. rMBGBMGi .7Y, 1i::i   vO0PMNNSXXEqP@Secbone.
//		        .i1r. .jkY,    vE. iY....  20Fq0q5X5F1S2F22uuv1M;
//============================当你妹的程序员啊======================

namespace Core;


use Conf\Config;
use Conf\Event;
use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Component\Di;
use Core\Component\Error\Trigger;
use Core\Component\Sys\ErrorHandler;
use Core\Component\Spl\SplError;
use Core\Component\SysConst;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\Server;
use Core\Utility\File;

class Core
{
    protected static $instance;
    private $preCall;
    function __construct($preCall)
    {
        $this->preCall = $preCall;
    }

    static function getInstance(callable $preCall = null){
        if(!isset(self::$instance)){
            self::$instance = new static($preCall);
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
        $this->preHandle();
        Event::getInstance()->frameInitialize();
        $this->sysDirectoryInit();
        Event::getInstance()->frameInitialized();
        $this->registerErrorHandler();
        return $this;
    }

    private function defineSysConst(){
        defined('ROOT') or define("ROOT",realpath(__DIR__.'/../'));
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
        }else{
            //创建默认Session存储目录
            $path = $tempDir."/Session";
            File::createDir($path);
            Di::getInstance()->set(SysConst::SESSION_SAVE_PATH,$path);
        }
        //创建日志目录
        $logDir = Di::getInstance()->get(SysConst::LOG_DIRECTORY);
        if(empty($logDir)){
            $logDir = ROOT."/Log";
            Di::getInstance()->set(SysConst::LOG_DIRECTORY,$logDir);
        }
        if(!File::createDir($logDir)){
            die("create log Directory:{$logDir} fail");
        }
        Config::getInstance()->setConf("SERVER.CONFIG.log_file",$logDir."/swoole.log");
        Config::getInstance()->setConf("SERVER.CONFIG.pid_file",$logDir."/pid.pid");
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
            set_error_handler(function($errorCode, $description, $file = null, $line = null, $context = null){
                Trigger::error($description,$file,$line,$errorCode,debug_backtrace());
            });
            register_shutdown_function(function (){
                $error = error_get_last();
                if(!empty($error)){
                    Trigger::error($error['message'],$error['file'],$error['line'],E_ERROR,debug_backtrace());
                    //HTTP下，发送致命错误时，原有进程无法按照预期结束链接,强制执行end
                    if(Request::getInstance()){
                        Response::getInstance()->end(true);
                    }
                }
            });
        }
    }
    private function preHandle(){
        if(is_callable($this->preCall)){
            call_user_func($this->preCall);
        }
        Di::getInstance()->set(SysConst::SESSION_NAME,'EasySwoole');
        Di::getInstance()->set(SysConst::VERSION,'1.1.1');
    }
}