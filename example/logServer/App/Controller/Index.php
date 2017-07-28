<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/26
 * Time: 上午10:11
 */

namespace App\Controller;


use App\Model\Log\SingletonCache;
use App\Model\Log\Writer;
use Core\AbstractInterface\AbstractController;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Swoole\AsyncTaskManager;

class Index extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        /*
         * 巧妙利用swoole常驻内存的优势，使用单例设计模式，
         * 缓存日志信息，至内存中，直至到达设置阈值时，利用异步进程进行真实IO写入
         * 避免密集IO写入，导致服务响应缓慢，从而提高日志服务器QPS。
         */
        $cache = SingletonCache::getInstance();
        $cache->add("msg at time ".microtime(true));
        if($cache->size() >= 10){
            $str = implode("\n",$cache->allLog());
            $cache->clear();
            AsyncTaskManager::getInstance()->add(function ()use($str){
                Writer::write($str);
            });
            Logger::console("really write log");
        }
        $this->response()->writeJson();
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFound() method.
    }

    function afterAction()
    {
        // TODO: Implement afterAction() method.
    }
}