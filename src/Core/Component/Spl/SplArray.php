<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/11
 * Time: 下午3:01
 */

namespace Core\Component\Spl;


class SplArray extends \ArrayObject
{
    public function __construct(array $array = array())
    {
        parent::__construct($array);
    }

    function __get($name)
    {
        // TODO: Implement __get() method.
        if(isset($this[$name])){
            if(is_array($this[$name])){
                return new SplString($this[$name]);
            }else{
                return $this[$name];
            }
        }else{
            return null;
        }
    }

    function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this[$name] = $value;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this,JSON_UNESCAPED_UNICODE,JSON_UNESCAPED_SLASHES);
    }
}