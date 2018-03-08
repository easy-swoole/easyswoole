<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/8
 * Time: 下午2:43
 */

namespace EasySwoole\Core\AbstractInterface;


abstract class AbstractCoroutinePool
{
    private static $instance;

    protected $minNum = 3;
    protected $maxNum = 10;
    protected $currentNum = 0;

    private $queue = null;

    function __construct()
    {
        $this->queue = new \SplQueue();
        for ($i=0 ; $i < $this->minNum ; $i++){
            $obj = $this->createObject();
            if($obj){
                $this->queue->enqueue($obj);
                $this->currentNum++;
            }
        }
    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getObj()
    {
        if($this->queue->isEmpty()){
            if($this->currentNum < $this->maxNum){
                $obj = $this->createObject();
                if($obj){
                    $this->currentNum++;
                }
                return $obj;
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
        $this->currentNum--;
    }
}