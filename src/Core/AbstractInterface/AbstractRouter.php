<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/3
 * Time: 下午8:21
 */

namespace Core\AbstractInterface;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\SysConst;
use Core\Http\Request;
use Core\Http\Response;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

abstract class AbstractRouter
{
    protected $isCache = false;
    protected $cacheFile;
    private $routeCollector;
    function __construct()
    {
        $this->routeCollector = new RouteCollector(new Std(),new GroupCountBased());
        $this->addRouter($this->routeCollector);
    }

    abstract function addRouter(RouteCollector $routeCollector);

    /*
     * to enable router file cache
     * @param $cacheFile
     */
    function enableCache($cacheFile = null){
        $this->isCache = true;
        if($cacheFile === null){
            $temp = Di::getInstance()->get(SysConst::TEMP_DIRECTORY);
            $this->cacheFile = $temp."/router.cache";
        }else{
            /*
             * suggest to set a file in memory path ，such as
             * /dev/shm/ in centos 6.x~7.x
             */
            $this->cacheFile = $cacheFile;
        }
    }

    /*
     * @return mixed cacheFile or boolean false
     */
    function isCache(){
        if($this->isCache){
            return $this->cacheFile;
        }else{
            return false;
        }
    }
    function getRouteCollector(){
        return $this->routeCollector;
    }
}