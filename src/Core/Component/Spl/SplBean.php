<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/29
 * Time: 下午1:54
 */

namespace Core\Component\Spl;


abstract class SplBean implements \JsonSerializable
{
    private $__varList = array();
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

    final function __toString()
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

    function toArray(array $columns = null,$filterCallBack = null){
        if(!is_a($filterCallBack,\Closure::class)){
            $filterCallBack = function ($val){
                return !is_null($val);
            };
        }
        if($columns){
            $data = $this->jsonSerialize();
            return array_filter(array_intersect_key($data, array_flip($columns)),$filterCallBack);
        }else{
            return array_filter($this->jsonSerialize(),$filterCallBack);
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