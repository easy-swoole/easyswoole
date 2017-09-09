<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/29
 * Time: 下午1:54
 */

namespace Core\Component\Spl;


use Core\Utility\Judge;

abstract class SplBean implements \JsonSerializable
{
    private $__varList = array();
    const FILTER_TYPE_NOT_NULL = 1;
    const FILTER_TYPE_NOT_EMPTY = 2;
    final function __construct($beanArray = array())
    {
        $this->__varList = $this->allVarKeys();
        $this->initialize();
        $this->arrayToBean($beanArray);
    }

    final protected function setDefault(&$property,$val){
        $property = $val;
        return $this;
    }

    final function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        $data = array();
        foreach ($this->__varList as $var){
            $data[$var] = $this->$var;
        }
        return $data;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->jsonSerialize(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    abstract protected function initialize();

    private function allVarKeys(){
        $data = get_class_vars(static::class);
        unset($data['__varList']);
        return array_keys($data);
    }

    function toArray($filterType = false,array $columns = null){
        if($columns){
            $data = $this->jsonSerialize();
            $ret = array_intersect_key($data, array_flip($columns));
        }else{
            $ret = $this->jsonSerialize();
        }
        if($filterType === self::FILTER_TYPE_NOT_NULL){
            return array_filter($ret,function ($val){
                return !is_null($val);
            });
        }else if($filterType === self::FILTER_TYPE_NOT_EMPTY){
            return array_filter($ret,function ($val){
                //0不为空
                return !Judge::isEmpty($val);
            });
        }else{
            return $ret;
        }
    }

    function arrayToBean(array $data){
        $data = array_intersect_key($data,array_flip($this->__varList));
        foreach ($data as $var => $val){
            $this->$var = $val;
        }
        return $this;
    }
}