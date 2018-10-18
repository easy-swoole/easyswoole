<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use App\Crontab\Cron;
use App\Vendor\Logger\LoggerHandler;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Crontab\CronTab;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\EventHelper;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;
use EasySwoole\Core\Utility\File;

Class EasySwooleEvent implements EventInterface {

    public static function loadConf($ConfPath)
    {
        $Conf  = Config::getInstance();
        $files = File::scanDir($ConfPath);
        foreach ($files as $file) {
            $data = require_once $file;
            $Conf->setConf(strtolower(basename($file, '.php')), (array)$data);
        }
    }

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        Di::getInstance()->set(SysConst::LOGGER_WRITER,LoggerHandler::class);

        // 载入项目 Conf 文件夹中所有的配置文件
        self::loadConf(EASYSWOOLE_ROOT . '/Conf');

        //异常拦截, 生产环境开启此配置, TODO 调试环境关闭有助于调试
//        Di::getInstance()->set( SysConst::HTTP_EXCEPTION_HANDLER, \App\ExceptionHandler::class );
//        Di::getInstance()->set(SysConst::CONTROLLER_MAX_DEPTH, 5);
    }

    public static function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        //针对websocket 注册ws onmessage事件
        EventHelper::registerDefaultOnMessage($register,\App\Parser::class);

        //hot reload start TODO 线上环境可以去掉
        $register->add($register::onWorkerStart, function(\swoole_server $server, int $workerId){
            if ($workerId == 0) {
                // 递归获取所有目录和文件
                $a = function ($dir) use (&$a) {
                    $data = array();
                    if (is_dir($dir)) {
                        //是目录的话，先增当前目录进去
                        $data[] = $dir;
                        $files = array_diff(scandir($dir), array('.', '..'));
                        foreach ($files as $file) {
                            $data = array_merge($data, $a($dir . "/" . $file));
                        }
                    } else {
                        $data[] = $dir;
                    }
                    return $data;
                };
                $list = $a(EASYSWOOLE_ROOT);
                $notify = inotify_init();
                // 为所有目录和文件添加inotify监视
                foreach ($list as $item) {
                    inotify_add_watch($notify, $item, IN_CREATE | IN_DELETE | IN_MODIFY);
                }
                // 加入EventLoop
                swoole_event_add($notify, function () use ($notify) {
                    $events = inotify_read($notify);
                    if (!empty($events)) {
                        //注意更新多个文件的间隔时间处理,防止一次更新了10个文件，重启了10次，懒得做了，反正原理在这里
                        ServerManager::getInstance()->getServer()->reload();
                    }
                });
            }
        });

        //hot reload end
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
        //memcached链接
        Di::getInstance()->set('MEMCACHED',\App\Vendor\Db\Memcached::class);

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