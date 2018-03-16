<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/15
 * Time: 下午5:20
 */

namespace EasySwoole\Core\Swoole\Coroutine;


use EasySwoole\Core\AbstractInterface\AbstractCoroutinePool;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Table;

class PoolManager
{
    protected $poolList = [];
    protected $processPool = [];
    const TABLE_NAME = 'coroutinePool';
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
            1024
        );
    }

    function addPool(string $class,int $minNum = 3,int $maxNum = 10)
    {
        try{
            $ref = new \ReflectionClass($class);
            if($ref->isSubclassOf(AbstractCoroutinePool::class)){
                $this->poolList[$class] = [
                    'min'=>$minNum,
                    'max'=>$maxNum,
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

    function getPool(string $class):?AbstractCoroutinePool
    {
        if(isset($this->processPool[$class])){
            return $this->processPool[$class];
        }else{
            //看看是否是当前进程未初始化的
            if(isset($this->poolList[$class])){
                if($this->init($class)){
                    return $this->getPool($class);
                }else{
                    return null;
                }
            }else{
                try{
                    $this->addPool($class);
                    if($this->init($class)){
                        return $this->getPool($class);
                    }else{
                        return null;
                    }
                }catch (\Throwable $throwable){
                    return null;
                }
            }
        }
    }

    public function workerStartClean($workerId)
    {
        foreach ($this->poolList as $class => $item){
            $key = self::generateTableKey($workerId);
            $table = TableManager::getInstance()->get(self::TABLE_NAME);
            $table->del($key);
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