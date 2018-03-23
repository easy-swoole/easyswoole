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
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH, 5);
    }

    public function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        $instance = Config::getInstance();
        $masterMysqlConf = $instance->getConf("MASTER_MYSQL");
        Di::getInstance()->set('MYSQL_MASTER',\MysqliDb::class, $masterMysqlConf);
        $slaveMysqlConf = $instance->getConf("SLAVE_MYSQL");
        Di::getInstance()->set('MYSQL_SLAVE',\MysqliDb::class, $slaveMysqlConf);
        $redisConf = $instance->getConf("REDIS");
        Di::getInstance()->set('REDIS',\App\Vendor\Db\Redis::class, $redisConf);
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