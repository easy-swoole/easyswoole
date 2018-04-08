<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/21
 * Time: 下午12:53
 */

namespace EasySwoole\Core\Swoole\Coroutine\AbstractInterface;
use EasySwoole\Core\Swoole\Coroutine\PoolManager;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Time\Timer;


abstract class CoroutinePool
{
    protected $minNum = 3;
    protected $maxNum = 10;
    private $queue = null;

    function __construct(int $min = 3,int $max = 10)
    {
        $table = TableManager::getInstance()->get(PoolManager::TABLE_NAME);
        $key = PoolManager::generateTableKey(static::class);
        $this->minNum = $min;
        $this->maxNum = $max;
        $this->queue = new \SplQueue();
        for ($i=0 ; $i < $this->minNum ; $i++){
            $obj = $this->createObject();
            if($obj){
                //currentNum表示当前某个对象全部创建的实例数量
                $table->incr($key,'currentNum');
                $this->queue->enqueue($obj);
            }
        }
    }

    public function getObj($timeOut = 0.1)
    {
        //优先检测队列是否为空
        $obj = null;
        if($this->queue->isEmpty()){
            $key = PoolManager::generateTableKey(static::class);
            $table = TableManager::getInstance()->get(PoolManager::TABLE_NAME);
            $testNum = $table->incr($key,'currentNum');
            if($testNum !== false){
                //若队列为空，则判断能否创建
                if($testNum <= $this->maxNum){
                    $obj = $this->createObject();
                }else{
                    $table->decr($key,'currentNum');
                }
                //若无创建，则继续等待协程调度，不过目前由于swoole协程调度不稳定，因此先预留$timeOut参数
            }
        }else{
            $obj = $this->queue->dequeue();
        }
        if($obj instanceof PoolObject){
            $obj->initialize();
        }
        return $obj;
    }

    public function freeObj($obj)
    {
        if(is_object($obj)){
            if($obj instanceof PoolObject){
                $obj->gc();
            }
            $this->queue->enqueue($obj);
        }
    }

    public function poolSize()
    {
        return $this->queue->count();
    }

    abstract protected function createObject();

    function destroyObj($obj){
        if($obj instanceof PoolObject){
            $obj->gc();
        }
        unset($obj);
        $table = TableManager::getInstance()->get(PoolManager::TABLE_NAME);
        $key = PoolManager::generateTableKey(static::class);
        $table->decr($key,'currentNum');
    }
}