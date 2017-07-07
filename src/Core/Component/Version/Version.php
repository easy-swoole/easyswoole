<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午2:30
 */

namespace Core\Component\Version;


class Version
{
    private $maps = array();
    private $defaultHandler;
    function addPathMap($rowPathInfo,$targetPathOrClosureHandler){
        $this->maps[$rowPathInfo] = $targetPathOrClosureHandler;
        return $this;
    }
    function setDefaultHandler($defaultPathOrClosureHandler){
        $this->defaultHandler = $defaultPathOrClosureHandler;
        return $this;
    }

    /**
     * @return array
     */
    public function getPathMaps()
    {
        return $this->maps;
    }

    public function getPathMap($rowPath){
        if(isset($this->maps[$rowPath])){
            return $this->maps[$rowPath];
        }else{
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getDefaultHandler()
    {
        return $this->defaultHandler;
    }


}