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
                $table->incr($key,'currentNum');
                $this->queue->enqueue($obj);
            }
        }
    }

    public function getObj($timeOut = 0.1)
    {
        if($this->queue->isEmpty()){
            $key = PoolManager::generateTableKey(static::class);
            $table = TableManager::getInstance()->get(PoolManager::TABLE_NAME);
            $testNum = $table->incr($key,'currentNum');
            if($testNum !== false){
                if($testNum <= $this->maxNum){
                    $obj = $this->createObject();
                    if($obj){
                        return $obj;
                    }else{
                        $table->decr($key,'currentNum');
                    }
                }else{
                    $table->decr($key,'currentNum');
                    $current = microtime(true);
                    while (1){
                        if(round((microtime(true) - $current),3) > $timeOut){
                            break;
                        }
                        \co::sleep(0.001);
                        if(!$this->queue->isEmpty()){
                            return $this->queue->dequeue();
                        }
                    }

                }
            }
            return null;
        }else{
            return $this->queue->dequeue();
        }
    }

    public function freeObj($obj)
    {
        if($obj){
            $this->queue->enqueue($obj);
        }
    }

    public function poolSize()
    {
        return $this->queue->count();
    }

    abstract protected function createObject();

    function destroyObj($obj){
        unset($obj);
        $table = TableManager::getInstance()->get(PoolManager::TABLE_NAME);
        $key = PoolManager::generateTableKey(static::class);
        $table->decr($key,'currentNum');
    }
}