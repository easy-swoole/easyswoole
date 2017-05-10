<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午1:07
 */

namespace Core\AbstractInterface;

use Core\Http\Request\Request;
use Core\Http\Response\Response;

abstract class AbstractController
{
    protected $actionName = null;
    protected $callArgs = null;
    function actionName($actionName = null){
        if($actionName === null){
            return $this->$actionName;
        }else{
            $this->$actionName = $actionName;
        }
    }
    abstract function index();
    abstract function onRequest($actionName);
    abstract function actionNotFount($actionName = null, $arguments = null);
    abstract function afterResponse();
    function request(){
        return Request::getInstance();
    }
    function response(){
        return Response::getInstance();
    }
    function __call($actionName, $arguments)
    {
        // TODO: Implement __call() method.
        $this->actionNotFount($actionName, $arguments);
    }
}