<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;

Class EasySwooleEvent implements EventInterface {

    public function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        Di::getInstance()->set(SysConst::CONTROLLER_MAX_DEPTH, 5);
    }

    public function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        $instance = Config::getInstance();
        //mysql主服务器
        $masterMysqlConf = $instance->getConf("MASTER_MYSQL");
        Di::getInstance()->set('MYSQL_MASTER',\MysqliDb::class, $masterMysqlConf);
        //mysql从服务器
        $slaveMysqlConf = $instance->getConf("SLAVE_MYSQL");
        Di::getInstance()->set('MYSQL_SLAVE',\MysqliDb::class, $slaveMysqlConf);
        //异步mysql主服务器
        Di::getInstance()->set('ASYNC_MYSQL_MASTER',\App\Vendor\Db\AsyncMysql::class);
        //redis客户端连接
        Di::getInstance()->set('REDIS',\App\Vendor\Db\Redis::class);
        //异步redis客户端
        Di::getInstance()->set('ASYNC_REDIS',\App\Vendor\Db\AsyncRedis::class);
    }

    public function onRequest(Request $request,Response $response): void
    {
        // TODO: Implement onRequest() method.
    }

    public function afterAction(Request $request,Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}