<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/3/14
 * Time: 16:59
 */

namespace Core\Component\AbstractInterface;


abstract class AbstractORM
{
    private $ORMMap = array();
    function __construct()
    {
        $this->setORM($this->ORMMap);
    }
	 /*
     * $orm must bu array(key(columnName)=>valName)
     */
    abstract protected function setORM(array &$orm);
	function objectToArray(array $columns = null){
        $data = array();
        if(!empty($columns)){
            foreach ($columns as $column){
                if(isset($this->ORMMap[$column])){
                    $valName = $this->ORMMap[$column];
                    $data[$column] = $this->$valName;
                }
            }
        }else{
            foreach ($this->ORMMap as $key => $value){
                $data[$key] = $this->$value;
            }
        }
        return $data;
    }
    function arrayToObject(array $data){
        foreach ($this->ORMMap as $key => $value){
            if(isset($data[$key])){
                $this->$value = $data[$key];
            }
        }
        return $this;
    }
    function mappingKeys(){
        return array_keys($this->ORMMap);
    }
    function mappingVariableList(){
        $data = array();
        foreach ($this->ORMMap as $key => $value){
            $data[] = $value;
        }
        return $data;
    }
    function mappingVariableValue($key){
        if(isset($this->ORMMap[$key])){
            $var = $this->ORMMap[$key];
            return $this->$var;
        }else{
            return null;
        }
    }
    function addMapping($key,$variableName){
        $this->ORMMap[$key] = $variableName;
		return $this;
    }
}