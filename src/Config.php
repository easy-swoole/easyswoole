<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午5:46
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\Config\AbstractConfig;
use EasySwoole\Config\TableConfig;

class Config
{
    private $conf;

    use Singleton;

    public function __construct(?AbstractConfig $config = null)
    {
        if($config == null){
            $config = new TableConfig();
        }
        $this->conf = $config;
    }

    function storageHandler(AbstractConfig $config = null):AbstractConfig
    {
        if($config){
            $this->conf = $config;
        }
        return $this->conf;
    }


    /**
     * 获取配置项
     * @param string $keyPath 配置项名称 支持点语法
     * @return array|mixed|null
     */
    public function getConf($keyPath = '')
    {
        if ($keyPath == '') {
            return $this->toArray();
        }
        return $this->conf->getConf($keyPath);
    }


    public function setConf($keyPath, $data): bool
    {
        return $this->conf->setConf($keyPath, $data);
    }


    public function toArray(): array
    {
        return $this->conf->getConf();
    }


    public function load(array $conf): bool
    {
        return $this->conf->load($conf);
    }

    public function merge(array $conf):bool
    {
        return $this->conf->merge($conf);
    }

    /**
     * 载入一个文件的配置项
     * @param string $filePath 配置文件路径
     */
    public function loadFile($filePath,bool $merge = true):bool
    {
        if (file_exists($filePath)) {
            $confData = include $filePath;
            if(is_array($confData)){
                if($merge){
                    $this->conf->merge($confData);
                }else{
                    $this->conf->load($confData);
                }
                return true;
            }
        }
        return false;
    }

    public function loadEnv(string $file,bool $merge = true):bool
    {
        if(file_exists($file)){
            $data = parse_ini_file($file,true);
            if(is_array($data)){
                if($merge){
                    $this->conf->merge($data);
                }else{
                    $this->conf->load($data);
                }
                return true;
            }
        }
        return false;
    }

    public function clear():bool
    {
        return $this->conf->clear();
    }
}
