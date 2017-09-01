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
    function addPathMap($rowPath,$targetPathOrClosureHandler){
        $rowPathInfo = $this->generatePathInfo($rowPath);
        if(is_string($targetPathOrClosureHandler)){
            $targetPathOrClosureHandler = $this->generatePathInfo($targetPathOrClosureHandler);
        }
        $this->maps[$rowPathInfo] = $targetPathOrClosureHandler;
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

    private function generatePathInfo($path){
        $basePath = dirname($path);
        $info = pathInfo($path);
        if($info['filename'] != 'index'){
            if($basePath == '/'){
                $basePath = $basePath.$info['filename'];
            }else{
                $basePath = $basePath.'/'.$info['filename'];
            }
        }
        return $basePath;
    }
}