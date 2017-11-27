<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/2
 * Time: 下午9:25
 */

namespace Core\Http\Session;


use Core\Component\Di;
use Core\Component\SysConst;
use Core\Http\Request as HttpRequest;
use Core\Http\Response as HttpResponse;
use Core\Swoole\AsyncTaskManager;
use Core\Utility\Random;

class Session
{
    private $sessionName;
    private $sessionSavePath;
    private $isStart = false;
    private $sessionHandler;
    private $sessionId;
    private static $staticInstance;

    public static function getInstance(){
        if(!isset(self::$staticInstance)){
            self::$staticInstance = new Session();
        }
        return self::$staticInstance;
    }

    function __construct()
    {
        $handler = Di::getInstance()->get(SysConst::SESSION_HANDLER);
        if($handler instanceof \SessionHandlerInterface){
            $this->sessionHandler = $handler;
        }else{
            $this->sessionHandler = new SessionHandler();
        }
        $this->init();
    }

    function sessionName($name = null){
        if($name == null){
            return $this->sessionName;
        }else{
            if($this->isStart){
                trigger_error("your can not change session name as {$name} when session is start");
                return false;
            }else{
                $this->sessionName = $name;
                return true;
            }
        }
    }

    function sessionId($sid = null){
        if($sid === null){
            return $this->sessionId;
        }else{
            if($this->isStart){
                trigger_error("your can not change session sid as {$sid} when session is start");
                return false;
            }else{
                $this->sessionId = $sid;
                return true;
            }
        }
    }

    function savePath($path = null){
        if($path == null){
            return $this->sessionSavePath;
        }else{
            if($this->isStart){
                trigger_error("your can not change session path as {$path} when session is start");
                return false;
            }else{
                $this->sessionSavePath = $path;
                return true;
            }
        }
    }

    function isStart(){
        return $this->isStart;
    }

    function start(){
        if(!$this->isStart){
            $boolean = $this->sessionHandler->open($this->sessionSavePath,$this->sessionName);
            if(!$boolean){
                trigger_error("session fail to open {$this->sessionSavePath} @ {$this->sessionName}");
                return false;
            }
            $probability = intval(Di::getInstance()->get(SysConst::SESSION_GC_PROBABILITY));
            $probability = $probability >= 30 ? $probability : 1000;
            if(mt_rand(0,$probability) == 1){
                $handler = clone $this->sessionHandler;
                AsyncTaskManager::getInstance()->add(function ()use ($handler){
                    $set = Di::getInstance()->get(SysConst::SESSION_GC_MAX_LIFE_TIME);
                    if(!empty($set)){
                        $maxLifeTime = $set;
                    }else{
                        $maxLifeTime = 3600*24*30;
                    }
                    $handler->gc($maxLifeTime);
                });
            }
            $request = HttpRequest::getInstance();
            $cookie = $request->getCookieParams($this->sessionName);
            if($this->sessionId){
                //预防提前指定sid
                if($this->sessionId != $cookie){
                    $data = array(
                        $this->sessionName=>$this->sessionId
                    );
                    $request->withCookieParams($request->getRequestParam()+$data);
                    HttpResponse::getInstance()->setCookie($this->sessionName,$this->sessionId);
                }
            }else{
                if($cookie === null){
                    $sid = $this->generateSid();
                    $data = array(
                        $this->sessionName=>$sid
                    );
                    $request->withCookieParams($request->getRequestParam()+$data);
                    HttpResponse::getInstance()->setCookie($this->sessionName,$sid);
                    $this->sessionId = $sid;
                }else{
                    $this->sessionId = $cookie;
                }
            }
            $this->isStart = 1;
            return true;
        }else{
            trigger_error('session has start');
            return false;
        }
    }

    function close(){
        if($this->isStart){
            $this->init();
            return $this->sessionHandler->close();
        }else{
            return true;
        }
    }

    private function init(){
        $di = Di::getInstance();
        $name = $di->get(SysConst::SESSION_NAME);
        $this->sessionName = $name ? $name : 'EasySwoole';
        $this->sessionSavePath = $di->get(SysConst::SESSION_SAVE_PATH);
        $this->sessionId = null;
        $this->isStart = false;
    }

    private function generateSid(){
        return md5(microtime().Random::randStr(3));
    }

    /*
     * 当执行read的时候，要求上锁
    */
    function read(){
        return $this->sessionHandler->read($this->sessionId);
    }

    function write($string){
        return $this->sessionHandler->write($this->sessionId,$string);
    }

    function destroy(){
         if($this->sessionHandler->destroy($this->sessionId)){
             return $this->close();
         }
         return false;
    }
}