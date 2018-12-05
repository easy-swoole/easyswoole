<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/9
 * Time: 14:45
 */

namespace App;


use App\Vendor\Logger\LoggerHandler;
use EasySwoole\Config;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

/**
 * 统一异常处理, 包括想屏蔽异常和记录异常, 这里在生产环境需要屏蔽所有异常, 同时将异常写入日志
 * Class ExceptionHandler
 * @package App
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var LoggerHandler
     */
    private $logger;

    public function handle(\Throwable $throwable, Request $request, Response $response)
    {
        if(Config::getInstance()->getConf("env") === "debug") { //开发环境会打印出message
            echo "\033[41m error： " . $throwable->getMessage() . "\033[0m \r\n\033[42m file：" . $throwable->getFile() . "\033[0m \r\n" . "\033[45m line：" . $throwable->getLine() . "\033[0m \r\n";
        }
        $this->logger = Di::getInstance()->get(SysConst::LOGGER_WRITER);
        $errData = array("errMsg" => $throwable->getMessage());
        $this->logger->error($errData, array(
            "file" => $throwable->getFile(),
            "line" => $throwable->getLine()
        ));
        //统一异常处理 TODO: Implement handle() method.
    }

}