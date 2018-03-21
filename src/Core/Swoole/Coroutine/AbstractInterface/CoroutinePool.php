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
    private $suspend = [];

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
                    $cid = \co::getUid();
                    $this->suspend[$cid] = $cid;
                    $suspend = true;
                    if ($timeOut > 0) {
                        Timer::delay($timeOut * 1000, function () use (&$suspend, $cid) {
                            if ($suspend) {
                                unset($this->suspend[$cid]);
                                \co::resume($cid);
                            }
                        });
                    }
                    \co::suspend($cid);
                    $suspend = false;
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
            if (count($this->suspend) > 0) {
                $cid = current($this->suspend);
                unset($this->suspend[$cid]);
                \co::resume($cid);
            }
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