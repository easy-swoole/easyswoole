<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/3
 * Time: 下午4:30
 */

namespace EasySwoole\Core\Component\Spl;

/*
 * 仅能获取protected 和public熟悉
 */
class SplBean implements \JsonSerializable
{
    const FILTER_NOT_NULL = 1;
    const FILTER_NOT_EMPTY = 2;//0 不算empty
    public function __construct(array $data = null,$autoCreateProperty = false)
    {
        if($data){
            $this->arrayToBean($data,$autoCreateProperty);
        }
        $this->initialize();
    }

    final public function allProperty():array
    {
        $data = [];
        foreach ($this as $key => $item){
            array_push($data,$key);
        }
        return $data;
    }

    function toArray(array $columns = null,$filter = null):array
    {
        $data = $this->jsonSerialize();
        if($columns){
            $data = array_intersect_key($data, array_flip($columns));
        }
        if($filter === self::FILTER_NOT_NULL){
            return array_filter($data,function ($val){
                return !is_null($val);
            });
        }else if($filter === self::FILTER_NOT_EMPTY){
            return array_filter($data,function ($val){
                if($val === 0 || $val === '0'){
                    return true;
                }else{
                    return !empty($val);
                }
            });
        }else if(is_callable($filter)){
            return array_filter($data,$filter);
        }
        return $data;
    }

    final public function arrayToBean(array $data,$autoCreateProperty = false):SplBean
    {
        if($autoCreateProperty == false){
            $data = array_intersect_key($data,array_flip($this->allProperty()));
        }
        foreach ($data as $key => $item){
            $this->addProperty($key,$item);
        }
        return $this;
    }

    final public function addProperty($name,$value = null):void
    {
        $this->$name = $value;
    }

    final public function getProperty($name)
    {
        if(isset($this->$name)){
            return $this->$name;
        }else{
            return null;
        }
    }

    final public function jsonSerialize():array
    {
        // TODO: Implement jsonSerialize() method.
        $data = [];
        foreach ($this as $key => $item){
            $data[$key] = $item;
        }
        return $data;
    }

    /*
     * 在子类中重写该方法，可以在类初始化的时候进行一些操作
     */
    protected function initialize():void
    {

    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->jsonSerialize(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function restore(array $data = [])
    {
        $this->arrayToBean($data+get_class_vars(static::class));
        $this->initialize();
        return $this;
    }

}