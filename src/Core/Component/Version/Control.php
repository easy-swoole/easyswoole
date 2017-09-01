<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午2:30
 */

namespace Core\Component\Version;


use Core\Http\Request;
use Core\Http\Response;
use Core\UrlParser;

class Control
{
    const CURRENT_VERSION = 'CURRENT_VERSION';
    const LAST_FORWARD_PATH = 'LAST_FORWARD_PATH';
    const IS_EXCEPT_CONTROL = 'IS_EXCEPT_CONTROL';
    private $map = array();
    /*
     * handler if match a version，must return boolean true
     */
    function addVersion($version,\Closure $judge){
        $temp = new Version();
        $this->map[$version] = array(
            "judge"=>$judge,
            'versionClass'=>$temp
        );
        return $temp;
    }
    function startControl(){
        $request = Request::getInstance();
        $response = Response::getInstance();
        if($response->isEndResponse()){
            return;
        }
        if($request->getAttribute(self::IS_EXCEPT_CONTROL)){
            return;
        }

        $targetVersion = null;
        $targetVersionClass = null;
        $currentPathInfo = UrlParser::pathInfo();
        foreach ($this->map as $version => $item){
            $flag = call_user_func($item['judge'],$request,$response);
            if($flag){
                $targetVersion = $version;
                $targetVersionClass = $item['versionClass'];
                break;
            }
        }

        if($targetVersion !== null){
            $handler = $targetVersionClass->getPathMap($currentPathInfo);
            $request->withAttribute(self::CURRENT_VERSION,$targetVersion);
            $request->withAttribute(self::LAST_FORWARD_PATH,$currentPathInfo);
            if($handler == $currentPathInfo){
                throw new \Exception("api version control for path:{$currentPathInfo} at version:{$targetVersion} is an endless loop");
            }else{
                if(is_string($handler)){
                    $response->forward($handler);
                    $response->end();
                }else if($handler instanceof \Closure){
                    call_user_func($handler,$request,$response);
                    $response->end();
                }
            }
        }
    }
}