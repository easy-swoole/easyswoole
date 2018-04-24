<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/24
 * Time: 下午9:01
 */

namespace EasySwoole\Core\Component\Spl;


class SplEnum
{
    const __DEFAULT = '__DEFAULT';

    private $val = self::__DEFAULT;
    private $name = '__DEFAULT';

    final public function __construct($val = null)
    {
        $list = $this->getConstants();
        if (count($list) != count(array_unique($list))) {
            $class = static::class;
            throw new \Exception("class : {$class} define duplicate value");
        }
        if($val !== null){
            $this->val = $val;
            $this->name = array_search($val,$list);
            if($this->name === false){
                throw new \Exception("invalid value");
            }
        }
    }

    final public function getName():string
    {
        return $this->name;
    }

    final public function getValue()
    {
        return $this->val;
    }

    private final function getConstants():array
    {
        try{
            return (new \ReflectionClass(static::class))->getConstants();
        }catch (\Throwable $throwable){
            return [];
        }
    }
}