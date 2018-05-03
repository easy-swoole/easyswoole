<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use App\Crontab\Cron;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Crontab\CronTab;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Swoole\EventHelper;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;

Class EasySwooleEvent implements EventInterface {

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');

        //异常拦截, 生产环境开启此配置, TODO 调试环境关闭有助于调试
//        Di::getInstance()->set( SysConst::HTTP_EXCEPTION_HANDLER, \App\ExceptionHandler::class );
//        Di::getInstance()->set(SysConst::CONTROLLER_MAX_DEPTH, 5);
    }

    public static function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        //针对websocket 注册ws onmessage事件
        EventHelper::registerDefaultOnMessage($register,\App\Parser::class);

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

        /**
         * crontab 定时任务
         */
//        CronTab::getInstance()->addRule('redisKeysRefresh','*/1 * * * *',function (){
//            Cron::refreshRedisKeys();
//        });
    }

    public static function onRequest(Request $request,Response $response): void
    {
        // TODO: Implement onRequest() method.
    }

    public static function afterAction(Request $request,Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}