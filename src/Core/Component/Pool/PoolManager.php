<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/3
 * Time: 下午1:21
 */

namespace EasySwoole\Core\Component\Pool;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Pool\AbstractInterface\Pool;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\Memory\TableManager;
use Swoole\Table;

class PoolManager
{
    use Singleton;

    private $poolTable = null;
    private $poolClassList = [];
    private $poolObjectList = [];

    const TYPE_ONLY_WORKER = 1;
    const TYPE_ONLY_TASK_WORKER = 2;
    const TYPE_ALL_WORKER = 3;

    function __construct()
    {
        TableManager::getInstance()->add('__PoolManager', [
            'createNum'=>['type'=>Table::TYPE_INT,'size'=>3]
        ],8192);
        $this->poolTable = TableManager::getInstance()->get('__PoolManager');

        $conf = Config::getInstance()->getConf('POOL_MANAGER');
        if(is_array($conf)){
            foreach ($conf as $class => $item){
                $this->registerPool($class,$item['min'],$item['max'],$item['type']);
            }
        }
    }

    function registerPool(string $class,$minNum,$maxNum,$type = self::TYPE_ONLY_WORKER)
    {
        try{
            $ref = new \ReflectionClass($class);
            if($ref->isSubclassOf(Pool::class)){
                $this->poolClassList[$class] = [
                    'min'=>$minNum,
                    'max'=>$maxNum,
                    'type'=>$type
                ];
                return true;
            }else{
                Trigger::throwable(new \Exception($class.' is not Pool class'));
            }
        }catch (\Throwable $throwable){
            Trigger::throwable($throwable);
        }
        return false;
    }

    function getPool(string $class):?Pool
    {
        if(isset($this->poolObjectList[$class])){
            return $this->poolObjectList[$class];
        }else{
            return null;
        }
    }

    /*
     * 为自定义进程预留
     */
    function __workerStartHook($workerId)
    {
        $workerNum = Config::getInstance()->getConf('MAIN_SERVER.SETTING.worker_num');
        foreach ($this->poolClassList as $class => $item){
            if($item['type'] === self::TYPE_ONLY_WORKER){
                if($workerId > ($workerNum -1)){
                    continue;
                }
            }else if($item['type'] === self::TYPE_ONLY_TASK_WORKER){
                if($workerId <= ($workerNum -1)){
                    continue;
                }
            }
            $key = self::generateTableKey($class, $workerId);
            $this->poolTable->del($key);
            $this->poolObjectList[$class] = new $class($item['min'],$item['max'],$key);
        }
    }


    function getPoolTable()
    {
        return $this->poolTable;
    }

    public static function generateTableKey(string $class,int $workerId):string
    {
        return substr(md5($class.$workerId),8,16);
    }

}