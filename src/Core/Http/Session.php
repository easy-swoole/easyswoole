<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/28
 * Time: 下午12:56
 */

namespace Core\Http;


use Core\Component\Di;
use Core\Component\SessionHandler;
use Core\Component\SysConst;
use Core\Http\Request\Request;
use Core\Http\Response\Response;
use Core\Utility\Random;

class Session
{
    private $sessionName;
    private static $instance;
    private $data = null;
    private $sessionHandler;
    private $isOpenSessionHandler = 0;
    private $dataIsChange = 0;
    private $hasReadData = 0;
    function __construct()
    {
        $sessionName = Di::getInstance()->get(SysConst::DI_SESSION_NAME);
        $sessionName = $sessionName ? $sessionName : "easyPHP";
        $handler = Di::getInstance()->get(SysConst::DI_SESSION_HANDLER);
        if(!is_a($handler,\SessionHandlerInterface::class)){
            $handler = new SessionHandler();
        }
        $this->sessionHandler = $handler;
        $this->sessionName($sessionName);
    }

    function sessionName($sessionName = null){
        if($sessionName === null){
            return $this->sessionName;
        }else{
            if($this->sessionName !== $sessionName){
                if($this->isOpenSessionHandler){
                    $this->dataPersistent();
                    $this->sessionHandler->close();
                }
                $this->sessionName = $sessionName;
                $this->sessionHandler->open(null,$sessionName);
                $this->isOpenSessionHandler = 1;
            }
        }
    }

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Session();
        }
        return self::$instance;
    }

    function set($key,$val){
        $this->readData();
        $this->data[$key] = $val;
        $this->markUpChange();
    }
    function get($key = null){
        $this->readData();
        if($key !== null){
            if(isset($this->data[$key])){
                return $this->data[$key];
            }else{
                return null;
            }
        }else{
            return $this->data;
        }
    }
    function clear(){
        $this->readData();
        $this->data = null;
        $this->markUpChange();
    }
    function close(){
        $this->dataPersistent();
        $this->sessionHandler->close();
        self::$instance = null;
    }
    function destroy(){
        $this->clear();
        Response::getInstance()->cookie()->setCookie($this->sessionName(),null);
    }
    function sessionId($Sid = null){
        if($Sid === null){
            $cookie = Request::getInstance()->cookie()->getCookie();
            if(!empty($cookie[$this->sessionName])){
                return $cookie;
            }else{
                $Sid = $this->generateSid();
                $cookie[$this->sessionName] = $Sid;
                Request::getInstance()->setSwooleRequestProperty("cookie",$cookie);
                Response::getInstance()->cookie()->setCookie($this->sessionName(),$Sid);
            }
        }else{
            $cookie = Request::getInstance()->cookie()->getCookie();
            $cookie[$this->sessionName] = $Sid;
            Request::getInstance()->setSwooleRequestProperty("cookie",$cookie);
            Response::getInstance()->cookie()->setCookie($this->sessionName(),$Sid);
        }
    }
    /*
     * @return string
    */
    private function generateSid(){
        return strtoupper(md5(microtime().Random::randStr(8)));
    }
    private function dataPersistent(){
        //避免造成io浪费
        if($this->dataIsChange){
            $this->sessionHandler->write($this->sessionId(),serialize($this->data));
            $this->hasReadData = 0;
            $this->dataIsChange = 0;
            $this->data = null;
        }
    }
    private function markUpChange(){
        $this->dataIsChange = 1;
    }
    private function readData(){
        if(!$this->hasReadData){
            $this->data = unserialize($this->sessionHandler->read($this->sessionId()));
            $this->hasReadData = 1;
        }
    }

}