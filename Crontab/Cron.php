<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/5/3
 * Time: 18:51
 */

namespace App\Crontab;


use EasySwoole\Core\Component\Di;

class Cron
{
    /**
     * @var array 需要定时清除的redis
     */
    private static $keys = array(
        "innovation_sms_count:"
    );

    /**
     *  每天定时情况redis对应的keys
     */
    public static function refreshRedisKeys(){
        /**
         * @var $redis \Redis
         */
        $redis = Di::getInstance()->get("REDIS")->getConnect();
        foreach (self::$keys as $key){
            $keysArr = $redis->keys($key."*");
            foreach ($keysArr as $item){
                $redis->del($item);
            }
        }
        file_put_contents(__DIR__."/crontab.log", date("Y-m-d H:i:s")." redis refresh finish!\n", FILE_APPEND);
    }
}