<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/24
 * Time: 下午9:01
 */

namespace EasySwoole\Core\Component\Spl;

/**
 * Class SplBean
 * 仅能获取 protected 和 public 属性
 * @alter   : evalor <master@evalor.cn>
 * @package EasySwoole\Core\Component\Spl
 */
class SplBean implements \JsonSerializable
{
    const FILTER_NOT_NULL  = 1;
    const FILTER_NOT_EMPTY = 2; // 0 不算empty

    public function __construct(array $data = null, $autoCreateProperty = false)
    {
        if ($data) {
            $this->arrayToBean($data, $autoCreateProperty);
        }
        $this->initialize();
    }

    /**
     * 获取所有的成员
     * @return array
     */
    final public function allProperty(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            array_push($data, $key);
        }
        return $data;
    }

    /**
     * 输出为数组
     * @param array|null $columns
     * @param mixed      $filter
     * @return array
     */
    function toArray(array $columns = null, $filter = null): array
    {
        $data = $this->jsonSerialize();
        if ($columns) {
            $data = array_intersect_key($data, array_flip($columns));
        }
        if ($filter === self::FILTER_NOT_NULL) {
            return array_filter($data, function ($val) {
                return !is_null($val);
            });
        } elseif ($filter === self::FILTER_NOT_EMPTY) {
            return array_filter($data, function ($val) {
                if ($val === 0 || $val === '0') {
                    return true;
                } else {
                    return !empty($val);
                }
            });
        } elseif (is_callable($filter)) {
            return array_filter($data, $filter);
        }
        return $data;
    }

    /**
     * 数组转为Bean
     * @param array $data
     * @param bool  $autoCreateProperty
     * @return SplBean
     */
    final public function arrayToBean(array $data, $autoCreateProperty = false): SplBean
    {
        if ($autoCreateProperty == false) {
            $data = array_intersect_key($data, array_flip($this->allProperty()));
        }
        foreach ($data as $key => $item) {
            $this->addProperty($key, $item);
        }
        return $this;
    }

    /**
     * 添加成员
     * @param string $name
     * @param mixed  $value
     */
    final public function addProperty($name, $value = null): void
    {
        $this->$name = $value;
    }

    /**
     * 获取成员
     * @param $name
     * @return null
     */
    final public function getProperty($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    /**
     * 序列化
     * @return array
     */
    final public function jsonSerialize(): array
    {
        // TODO: Implement jsonSerialize() method.
        $data = [];
        foreach ($this as $key => $item) {
            $data[$key] = $item;
        }
        return $data;
    }

    /**
     * 在子类中重写该方法，可以在类初始化的时候进行一些操作
     */
    protected function initialize(): void
    {

    }

    /**
     * 转为字符串输出
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 使用数据重置Bean
     * @param array $data
     * @return $this
     */
    public function restore(array $data = [])
    {
        $this->arrayToBean($data + get_class_vars(static::class));
        $this->initialize();
        return $this;
    }

}