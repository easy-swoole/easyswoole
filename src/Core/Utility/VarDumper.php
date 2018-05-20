<?php

namespace EasySwoole\Core\Utility;

/**
 * 变量输出助手
 * Class VarDumper
 * @author : evalor <master@evalor.cn>
 */
class VarDumper
{
    /**
     * 将一个变量转为字符串表达
     * @param mixed $var 需要输出的变量
     * @return string
     * @throws \ReflectionException
     * @author : evalor <master@evalor.cn>
     */
    public static function dump($var)
    {
        switch (gettype($var)) {
            case 'NULL':
                return 'NULL';
            case 'boolean':
                return $var ? 'bool(true)' : 'bool(false)';
            case 'integer' :
                return "int($var)";
            case 'double' :
                return "float($var)";
            case 'string' :
                return sprintf("string(%d) \"%s\"", strlen($var), $var);
            case 'resource' :
                return self::dumpResource($var);
            case 'array':
                return self::dumpArray($var, '', 0) . '}';
            case 'object':
                return self::dumpObject($var, '', 0);
            case 'closure':
                return 'closure';
            default :
                return 'unknown';
        }
    }

    /**
     * 从资源中导出数据
     * @param $resource
     * @author : evalor <master@evalor.cn>
     * @return string
     */
    protected static function dumpResource($resource)
    {
        $resourceName = get_resource_type($resource);
        $resourceNumber = str_replace('Resource id #', '', strval($resource));
        return "resource({$resourceNumber}) of type ({$resourceName})";
    }

    /**
     * 从数组中导出数据
     * @param array  $array   待导出的数组
     * @param string $content 递归内容保存
     * @param int    $indent  缩进量
     * @author : evalor <master@evalor.cn>
     * @return string
     * @throws \ReflectionException
     */
    protected static function dumpArray($array, $content = '', $indent = 0)
    {
        if ($indent >= 12) return $content;
        $len = count($array);
        $content = str_repeat(' ', $indent) . "array({$len}) {" . PHP_EOL;
        $indent = $indent + 2;  // 每一层嵌套都需要增加2位缩进
        foreach ($array as $key => $value) {
            $keyStr = is_string($key) ? "\"$key\"" : $key;
            $content .= str_repeat(' ', $indent) . "[$keyStr]=>" . PHP_EOL;
            if (!is_array($value) && !is_object($value)) {
                // 其他直接解析
                $content .= str_repeat(' ', $indent) . self::dump($value) . PHP_EOL;
            } elseif (is_array($value)) {
                // 数组递归解析
                $content .= self::dumpArray($value, '', $indent) . str_repeat(' ', $indent) . '}' . PHP_EOL;
            } elseif (is_object($value)) {
                // 对象单独解析
                $content .= self::dumpObject($value, '', $indent) . str_repeat(' ', $indent) . PHP_EOL;
            }
        }
        return $content;
    }

    /**
     * 从对象中导出数据
     * @param object $object
     * @param string $content
     * @param int    $indent
     * @return string
     * @author : evalor <master@evalor.cn>
     * @throws \ReflectionException
     */
    protected static function dumpObject($object, $content = '', $indent = 0)
    {
        if ($indent >= 12) return str_repeat(' ', $indent) . '...';
        $ref = new \ReflectionClass($object);
        $properties = $ref->getProperties();
        $content .= str_repeat(' ', $indent) . sprintf("object(%s) (%d) {" . PHP_EOL, $ref->name, count($properties));
        $indent = $indent + 2;  // 每一层嵌套都需要增加2位缩进
        foreach ($properties as $key => $property) {
            $name = "\"$property->name\"";
            if (!$property->isStatic()) {
                if ($property->isPrivate()) $name = "{$name}:\"{$ref->name}\":private";
                if ($property->isProtected()) $name = "{$name}:protected";
                $property->setAccessible(true);
                $value = $property->getValue($object);
            } else {
                $property->setAccessible(true);
                $value = $ref->getDefaultProperties()[$property->name];
            }
            $content .= str_repeat(' ', $indent) . "[{$name}]=>" . PHP_EOL;

            if (!is_array($value) && !is_object($value)) {
                // 其他直接解析
                $content .= str_repeat(' ', $indent) . self::dump($value) . PHP_EOL;
            } elseif (is_array($value)) {
                // 数组递归解析
                $content .= self::dumpArray($value, '', $indent) . str_repeat(' ', $indent) . '}' . PHP_EOL;
            } elseif (is_object($value)) {
                // 对象单独解析
                $content .= self::dumpObject($value, '', $indent) . str_repeat(' ', $indent) . PHP_EOL;
            }
        }
        return $content . str_repeat(' ', $indent) . '}';
    }
}