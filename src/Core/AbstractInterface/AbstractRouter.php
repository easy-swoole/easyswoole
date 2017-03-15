<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/3
 * Time: 下午8:21
 */

namespace Core\AbstractInterface;
use Core\Component\RouteCollector;

abstract class AbstractRouter
{
    protected $isCache = false;
    protected $cacheFile;
    function __construct()
    {
        $this->addRouter(RouteCollector::getInstance());
    }

    abstract function addRouter(RouteCollector $routeCollector);

    /*
     * to enable router file cache
     * @param $cacheFile
     */
    function enableCache($cacheFile = null){
        $this->isCache = true;
        if($cacheFile === null){
            $this->cacheFile = ROOT."/Temp/router.cache";
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
}