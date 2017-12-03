<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/3
 * Time: 下午5:50
 */

namespace EasySwoole;


class AutoLoader
{
    private static $instance;
    private $prefixes = array();
    static function getInstance():AutoLoader
    {
        if(!isset(self::$instance)){
            self::$instance = new AutoLoader();
        }
        return self::$instance;
    }

    function __construct()
    {
        defined('ROOT') or define("ROOT",realpath(__DIR__));
        spl_autoload_register(array($this, 'loadClass'));
    }


    private function loadClass($class):bool
    {
        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return true;
            }
            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }


    public function addNamespace($prefix,$base_dir,$clear = false,$prepend = false):AutoLoader
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }
        if($clear == 1){
            //执行清空
            $this->prefixes[$prefix] = array();
        }
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
        return $this;
    }

    protected function loadMappedFile($prefix,$relative_class):bool
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if ($this->requireFile($file)) {
                return true;
            }
        }
        return false;
    }

    function requireFile($file):bool
    {
        /*
         * 若不加ROOT，会导致在daemonize模式下
         * 类文件引入目录错误导致类无法正常加载
         */
        $file = ROOT.'/'.$file;
        if (file_exists($file)) {
            require_once($file);
            return true;
        }
        return false;
    }

    function importPath($path,$ext = 'php'):void
    {
        $path = rtrim($path,'/');
        $pat = $path.'/*.'.$ext;
        foreach (glob($pat) as $file){
            $this->requireFile($file);
        }
    }
}