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
    private $beanColumns = array();
    private $propertyList = array();

    abstract function initialize();
    final function __construct(array $data = array())
    {
        $this->initialize();
        $this->propertyList = $this->getClassVars();
        $this->arrayToBean($data);
    }

    protected final function addBeanColumn($propertyName,$beanClassName,$arrayContainer = false){
        if(property_exists(static::class,$propertyName)){
            $this->beanColumns[$propertyName] = array(
                "class"=>$beanClassName,
                "isArrayContainer"=>$arrayContainer
            );
        }else{
            trigger_error("SplBean class ".static::class."@{$propertyName} not exist");
        }
        return $this;
    }


    final function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        $result = array();
        foreach ($this->propertyList as $propertyName){
            $data = $this->$propertyName;
            //判断是否在bean列表内
            if(array_key_exists($propertyName,$this->beanColumns)){
                if(!empty($data)){
                    if(is_array($data)){
                        //说明是beanArray
                        $temp = array();
                        foreach ($data as $key => $item){
                            if($item instanceof SplBean){
                                $temp[$key] = $item->jsonSerialize();
                            }else{
                                trigger_error("SplBean class ".static::class."@{$propertyName} sub Index {$key} expect SplBean Instance or SplBeanArray,".gettype($item)." given");
                                $temp[$key] = $item;
                            }
                        }
                    }elseif ($data instanceof SplBean){
                        $data = $data->jsonSerialize();
                    }else{
                        trigger_error("SplBean class ".static::class."@{$propertyName} expect SplBean Instance or SplBeanArray,".gettype($data)." given");
                    }
                }else{
                    //由于已经是设定为 bean 字段，所以在为空时  默认给变成 空数组
                    $data = array();
                }
            }else if(is_object($data)){
                $data = json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }
            $result[$propertyName] = $data;
        }
        return $result;
    }

    final function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->jsonSerialize(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    final function toArray(array $columns = array()){
        $data = $this->jsonSerialize();
        if(!empty($columns)){
            $result = array();
            foreach ($columns as $column){
                if(isset($data[$column])){
                    $result[$column] = $data[$column];
                }
            }
            return $result;
        }else{
            return $data;
        }
    }

    final function arrayToBean(array $data){
        foreach ($data as $propertyName => $value){
            if(array_key_exists($propertyName,$this->beanColumns)){
                $isArrayContainer = $this->beanColumns[$propertyName]['isArrayContainer'];
                $targetClass = $this->beanColumns[$propertyName]['class'];
                if(class_exists($targetClass)){
                    if(!empty($value)){
                        if($isArrayContainer){
                            $temp = array();
                            foreach ($value as $valueKey => $sub){
                                if(is_array($sub) && (!empty($sub))){
                                    $temp[$valueKey] =  new $targetClass($sub);
                                }else{
                                    $temp[$valueKey] =  new $targetClass();
                                }
                            }
                            $this->$propertyName = $temp;
                        }else{
                            $this->$propertyName = new $targetClass($value);
                        }
                    }else{
                        //空数据的时候
                        if($isArrayContainer){
                            $this->$propertyName = array();
                        }else{
                            $this->$propertyName = new $targetClass();
                        }
                    }
                }else{
                    $this->$propertyName = null;
                    trigger_error("SplBean class ".static::class."@{$propertyName} mapping class : {$targetClass} not exist");
                }
            }else{
                $this->$propertyName = $value;
            }
        }
        return $this;
    }

    private function getClassVars(){
        $temp =  array_keys(get_class_vars(static::class));
        $except = array(
            "propertyList","beanColumns"
        );
        foreach ($except as $item){
            $key = array_search($item,$temp);
            unset($temp[$key]);
        }
        return $temp;
    }
}