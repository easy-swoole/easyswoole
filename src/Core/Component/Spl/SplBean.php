<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/29
 * Time: 下午1:54
 */

namespace Core\Component\Spl;


class SplBean
{
    final function __construct(array $arrayData = null)
    {
        if($arrayData !== null){
            $allVars = $this->getClassVars();
            foreach ($allVars as $key){
                if(isset($arrayData[$key])){
                    $this->$key = $arrayData[$key];
                }
            }
        }
    }

    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        $data = array();
        $vars =  $this->getClassVars();
        foreach ($vars as $key){
            $data[$key] = $this->$key;
        }
        return $data;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->jsonSerialize(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    function toArray(array $propertyList = null){
        if($propertyList !== null){
            $data = array();
            $vars = $this->getClassVars();
            foreach ($vars as $var){
                if(in_array($var,$propertyList)){
                    $data[$var] = $this->$var;
                }
            }
            return $data;
        }else{
            return $this->jsonSerialize();
        }
    }

    private function getClassVars(){
        return array_keys(get_class_vars(static::class));
    }
}