<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/8
 * Time: 上午12:13
 */

namespace App\Controller\Test;


use Core\Component\Logger;
use Core\Swoole\AsyncTaskManager;
use Core\Swoole\SwooleHttpServer;

class Websocket extends AbstractController
{
    function index()
    {
        /*
         * url: /test/websocket
         */
        $this->smartyDisplay("websocket_client.html");
    }
    function push(){
        /*
         * url :/test/websocket/push/index.html?fd=xxxx
         */
        $fd = $this->request()->getRequestParam("fd");
        $info =  SwooleHttpServer::getInstance()->getServer()->connection_info($fd);
        if($info['websocket_status']){
            Logger::console("push data to client {$fd}");
            SwooleHttpServer::getInstance()->getServer()->push($fd,"data from server at ".time());
            $this->response()->write("push to fd :{$fd}");
        }else{
            $this->response()->write("fd {$fd} not a websocket");
        }
    }
    function connectionList(){
        /*
         * url:/test/websocket/connectionList/index.html
         * 注意   本example未引入redis来做fd信息记录，因此每次采用遍历的形式来获取结果，
         * 仅供思路参考，不建议在生产环节使用
         */
        $list = array();
        foreach (SwooleHttpServer::getInstance()->getServer()->connections as $connection){
            $info =  SwooleHttpServer::getInstance()->getServer()->connection_info($connection);
            if($info['websocket_status']){
                $list[] = $connection;
            }
        }
        $this->response()->writeJson(200,$list,"this is all websocket list");
    }
    function broadcast(){
        /*
         * url :/test/websocket/broadcast/index.html?fds=xx,xx,xx
         */
        $fds = $this->request()->getRequestParam("fds");
        $fds = explode(",",$fds);
        AsyncTaskManager::getInstance()->add(function ()use ($fds){
            foreach ( $fds as $fd) {
                SwooleHttpServer::getInstance()->getServer()->push($fd,"this is broadcast");
            }
        });
        $this->response()->write('broadcast to all client');
    }
}