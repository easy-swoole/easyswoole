<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/15
 * Time: 下午5:20
 */

namespace EasySwoole\Core\Swoole\Coroutine;
use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\Coroutine\AbstractInterface\CoroutinePool;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Table;


class PoolManager
{
    protected $poolList = [];
    protected $processPool = [];
    const TABLE_NAME = 'coroutinePool';
    const TYPE_ONLY_WORKER = 1;
    const TYPE_ONLY_TASK_WORKER = 2;
    const TYPE_ALL_WORKER = 3;
    use Singleton;
    /*
     * 协程仅能在worker中使用
     */
    function __construct()
    {
        TableManager::getInstance()->add(
            self::TABLE_NAME,
            [
                'currentNum'=>['type'=>Table::TYPE_INT,'size'=>2],
            ],
            4096
        );
    }

    function addPool(string $class,int $minNum = 3,int $maxNum = 10,$type = self::TYPE_ONLY_WORKER)
    {
        try{
            $ref = new \ReflectionClass($class);
            if($ref->isSubclassOf(CoroutinePool::class)){
                $this->poolList[$class] = [
                    'min'=>$minNum,
                    'max'=>$maxNum,
                    'type'=>$type
                ];
                return true;
            }else{
                Trigger::throwable(new \Exception($class.' is not AbstractCoroutinePool class'));
            }
        }catch (\Throwable $throwable){
            Trigger::throwable($throwable);
        }
        return false;
    }

    /*
     * 在worker start 对每个进程内的对象（连接）进行提前创建，避免第一个用户请求的时候花费了大量时间在对象创建上
     */
    protected function init(string $class)
    {
        /*
         * 对该进程内数据做table检验，若存在key则说明已经创建
         */
        $key = self::generateTableKey($class);
        $table = TableManager::getInstance()->get(self::TABLE_NAME);
        //inc与dec为队列原子操作
        if($table->incr($key,'currentNum') === 1){
            $item = $this->poolList[$class];
            $obj = new $class($item['min'],$item['max']);
            $this->processPool[$class] = $obj;
            $table->decr($key,'currentNum');
            return true;
        }else{
            $table->decr($key,'currentNum');
            return false;
        }
    }

    function getPool(string $class):?CoroutinePool
    {
        if(isset($this->processPool[$class])){
            return $this->processPool[$class];
        }else{
            return null;
        }
    }

    public function workerStartClean($workerId)
    {
        $workerNum = Config::getInstance()->getConf('MAIN_SERVER.SETTING.worker_num');
        foreach ($this->poolList as $class => $item){
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
            $table = TableManager::getInstance()->get(self::TABLE_NAME);
            $table->del($key);
            $this->init($class);
        }
    }

    static function generateTableKey(string $class,int $workerId = null):string
    {
        if($workerId === null){
            $workerId = ServerManager::getInstance()->getServer()->worker_id;
        }
        return substr(md5($class.$workerId),8,16);
    }
}