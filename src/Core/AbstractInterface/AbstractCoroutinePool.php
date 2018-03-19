<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/8
 * Time: 下午2:43
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Swoole\Coroutine\PoolManager;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\ServerManager;

abstract class AbstractCoroutinePool
{
    protected $minNum = 3;
    protected $maxNum = 10;
    private $queue = null;

    function __construct(int $min = 3,int $max = 20)
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
                    \co::sleep($timeOut);
                    if(!$this->queue->isEmpty()){
                        return $this->queue->dequeue();
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