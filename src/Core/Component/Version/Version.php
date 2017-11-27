<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/22
 * Time: 下午9:57
 */

namespace Core\Component\Version;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;


class Version
{
    private $versionName;
    private $judge;
    private $routeCollector;
    private $dispatcher = null;
    private $defaultHandler = null;
    function __construct($versionName,callable $judge)
    {
        $this->versionName = $versionName;
        $this->judge = $judge;
        $this->routeCollector = new RouteCollector(new Std(),new GroupCountBased());
    }

    function register(){
        return $this->routeCollector;
    }


    function dispatch($urlPath,$requestMethod){
        if($this->dispatcher == null){
            $this->dispatcher = new Dispatcher($this->routeCollector->getData());
        }
        return $this->dispatcher->dispatch($requestMethod,$urlPath);
    }

    /**
     * @return mixed
     */
    public function getVersionName()
    {
        return $this->versionName;
    }

    /**
     * @return callable
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * @return null
     */
    public function getDefaultHandler()
    {
        return $this->defaultHandler;
    }




}