<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/3
 * Time: 下午4:08
 */

namespace EasySwoole\Core\Component\Spl;

class SplArray extends \ArrayObject
{
    function __get($name)
    {
        // TODO: Implement __get() method.
        if (isset($this[$name])) {
            return $this[$name];
        } else {
            return null;
        }
    }

    function __set($name, $value): void
    {
        // TODO: Implement __set() method.
        $this[$name] = $value;
    }

    function __toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES);
    }

    function getArrayCopy(): array
    {
        return (array)$this;
    }

    function set($path, $value): void
    {
        $path = explode(".", $path);
        $temp = $this;
        while ($key = array_shift($path)) {
            $temp = &$temp[$key];
        }
        $temp = $value;
    }

    function get($path)
    {
        $paths = explode(".", $path);
        $data = $this->getArrayCopy();
        while ($key = array_shift($paths)){
            if(isset($data[$key])){
                $data = $data[$key];
            }else{
                return null;
            }
        }
        return $data;
    }

    public function delete($key): void
    {
        $path = explode(".", $key);
        $lastKey = array_pop($path);
        $data = $this->getArrayCopy();
        $copy = &$data;
        while ($key = array_shift($path)){
            if(isset($copy[$key])){
                $copy = &$copy[$key];
            }else{
                return;
            }
        }
        unset($copy[$lastKey]);
        parent::__construct($data);
    }

    /**
     * 数组去重取唯一的值
     * @return SplArray
     */
    public function unique(): SplArray
    {
        return new SplArray(array_unique($this->getArrayCopy()));
    }

    /**
     * 获取数组中重复的值
     * @return SplArray
     */
    public function multiple(): SplArray
    {
        $unique_arr = array_unique($this->getArrayCopy());
        return new SplArray(array_diff_assoc($this->getArrayCopy(), $unique_arr));
    }

    /**
     * 按照键值升序
     * @return SplArray
     */
    public function asort(): SplArray
    {
        parent::asort();
        return $this;
    }

    /**
     * 按照键升序
     * @return SplArray
     */
    public function ksort(): SplArray
    {
        parent::ksort();
        return $this;
    }

    /**
     * 自定义排序
     * @param int $sort_flags
     * @return SplArray
     */
    public function sort($sort_flags = SORT_REGULAR): SplArray
    {
        $temp = $this->getArrayCopy();
        sort($temp, $sort_flags);
        return new SplArray($temp);
    }

    /**
     * 取得某一列
     * @param string      $column
     * @param null|string $index_key
     * @return SplArray
     */
    public function column($column, $index_key = null): SplArray
    {
        return new SplArray(array_column($this->getArrayCopy(), $column, $index_key));
    }

    /**
     * 交换数组中的键和值
     * @return SplArray
     */
    public function flip(): SplArray
    {
        return new SplArray(array_flip($this->getArrayCopy()));
    }

    /**
     * 过滤本数组
     * @param string|array $keys    需要取得/排除的键
     * @param bool         $exclude true则排除设置的键名 false则仅获取设置的键名
     * @return SplArray
     */
    public function filter($keys, $exclude = false): SplArray
    {
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }
        $new = array();
        foreach ($this->getArrayCopy() as $name => $value) {
            if (!$exclude) {
                in_array($name, $keys) ? $new[$name] = $value : null;
            } else {
                in_array($name, $keys) ? null : $new[$name] = $value;
            }
        }
        return new SplArray($new);
    }

    /**
     * 提取数组中的键
     * @return SplArray
     */
    public function keys(): SplArray
    {
        return new SplArray(array_keys($this->getArrayCopy()));
    }

    /**
     * 提取数组中的值
     * @return SplArray
     */
    public function values(): SplArray
    {
        return new SplArray(array_values($this->getArrayCopy()));
    }

    public function flush():SplArray
    {
        foreach ($this as $key => $item){
            unset($this[$key]);
        }
        return $this;
    }

    public function loadArray(array $data)
    {
        parent::__construct($data);
    }
}