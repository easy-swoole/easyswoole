<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/3/14
 * Time: 16:59
 */

namespace Core\Component\AbstractInterface;


abstract class AbstractORM{
    /*
     * 注意   若想实现类似 json_encode(class) 自动转数组
     *    在继承类中   对应的变量名称请为public属性或者直接不定义，MAP会自动定义
     */
    private $ORMMap = array();
    /*
        * $data must bu array($propertyName=>value)
    */
    function __construct(array $data = array())
    {
        $this->setORM($this->ORMMap);
        $this->ORMMapHandler();
        if(!empty($data)){
            $this->arrayToObject($data);
        }
    }
    /*
     * $orm must bu like  array(propertyName =>columnName,propertyName2=>array(columnName2,defaultValue))
    */
    abstract protected function setORM(array &$orm);
    /*
     * @return array   array(propertyName => value);
     */
    function objectToArray(array $columns = null){
        $data = array();
        if(!empty($columns)){
            foreach ($this->ORMMap as $propertyName => $columnName){
                if(in_array($columnName,$columns)){
                    $data[$propertyName] = $this->$propertyName;
                }
            }
        }else{
            foreach ($this->ORMMap as $propertyName => $columnName){
                $data[$propertyName] = $this->$propertyName;
            }
        }
        return $data;
    }
    /*
     * $data must bu array($propertyName=>value)
     */
    function arrayToObject(array $data){
        foreach ($this->ORMMap as $propertyName => $columnName){
            if(isset($data[$propertyName])){
                $this->$propertyName = $data[$propertyName];
            }
        }
        return $this;
    }
    function mappingColumns(){
        $data = array();
        foreach ($this->ORMMap as $propertyName => $columnName){
            $data[] = $columnName;
        }
        return $data;
    }
    function mappingVariableList(){
        return array_keys($this->ORMMap);
    }
    function getMappingColumnValue($columnName){
        foreach ($this->ORMMap as $propertyName => $columnName2){
            if($columnName === $columnName2){
                return $this->$propertyName;
            }
        }
        return null;
    }
    function addMapping($propertyName,$columnName){
        $this->ORMMap[$propertyName] = $columnName;
        return $this;
    }
    function getPropertyMappingColumn($propertyName){
        if(isset($propertyName)){
            return $this->ORMMap[$propertyName];
        }
        return null;
    }
    function getPropertyValue($propertyName){
        return $this->$propertyName;
    }
    private function ORMMapHandler(){
        foreach ($this->ORMMap as $propertyName => $item){
            if(is_array($item)){
                $this->ORMMap[$propertyName] = $item[0];
                $this->$propertyName = $item[1];;
            }else{
                $this->$propertyName = null;
            }
        }
    }
    function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this);
    }
    /*
     * @param $arr multi array for array($propertyName=>value)
     */
    static function multiArrayToObject(array $arr){
        $data = array();
        foreach($arr as $item){
            array_push($data,new static($item));
        }
        return $data;
    }
}