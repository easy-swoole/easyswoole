<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/7
 * Time: 上午11:34
 */

namespace EasySwoole\Core\Http\Session;


use EasySwoole\Config;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Swoole\Task\TaskManager;
use EasySwoole\Core\Utility\Random;

class Session
{
    private $isStart = false;
    private $sessionSavePath;
    private $sessionName = 'EasySwoole';
    private $sessionId;
    private $sessionHandler;

    private $request;
    private $response;

    private $sessionData = null;

    function __construct(Request $request,Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $di = Di::getInstance()->get(SysConst::HTTP_SESSION_HANDLER);
        if($di instanceof \SessionHandlerInterface){
            $this->sessionHandler = $di;
        }else{
            $this->sessionHandler = new Handler();
        }
        $path = Di::getInstance()->get(SysConst::HTTP_SESSION_SAVE_PATH);
        if(empty($path)){
            $this->sessionSavePath = Config::getInstance()->getConf('TEMP_DIR').'/Session';
            if(!is_dir($this->sessionSavePath)){
                mkdir( $this->sessionSavePath );
            }
        }
    }

    function sessionName($name = null)
    {
        if($name == null){
            return $this->sessionName;
        }else{
            if($this->isStart){
                Trigger::error("your can not change session name as {$name} when session is start");
                return false;
            }else{
                $this->sessionName = $name;
                return true;
            }
        }
    }

    function sessionId($sid = null)
    {
        if($sid === null){
            return $this->sessionId;
        }else{
            if($this->isStart){
                Trigger::error("your can not change session sid as {$sid} when session is start");
                return false;
            }else{
                $this->sessionId = $sid;
                return true;
            }
        }
    }

    function isStart()
    {
        return $this->isStart;
    }

    function sessionStart():bool
    {
        if($this->isStart){
            return true;
        }else{
            $boolean = $this->sessionHandler->open($this->sessionSavePath,$this->sessionName);
            if(!$boolean){
                Trigger::error("session fail to open {$this->sessionSavePath} @ {$this->sessionName}");
                return false;
            }
            $probability = intval(Di::getInstance()->get(SysConst::HTTP_SESSION_GC_PROBABILITY));
            $probability = $probability >= 30 ? $probability : 1000;
            if(mt_rand(0,$probability) == 1){
                $handler = get_class($this->sessionHandler);
                $savePath = $this->sessionSavePath;
                $name = $this->sessionName;
                TaskManager::async(function ()use ($handler,$savePath,$name){
                    $handler = new Handler();
                    $handler->open($savePath,$name);
                    $set = Di::getInstance()->get(SysConst::HTTP_SESSION_GC_MAX_LIFE_TIME);
                    if(!empty($set)){
                        $maxLifeTime = $set;
                    }else{
                        $maxLifeTime = 3600*24*30;
                    }
                    $handler->gc($maxLifeTime);
                });
            }
            $cookie = $this->request->getCookieParams($this->sessionName);
            if($this->sessionId){
                //预防提前指定sid
                if($this->sessionId != $cookie){
                    $data = array(
                        $this->sessionName=>$this->sessionId
                    );
                    $this->request->withCookieParams($this->request->getRequestParam()+$data);
                    $this->response->setCookie($this->sessionName,$this->sessionId);
                }
            }else{
                if($cookie === null){
                    $sid = $this->generateSid();
                    $data = array(
                        $this->sessionName=>$sid
                    );
                    $this->request->withCookieParams($this->request->getRequestParam()+$data);
                    $this->response->setCookie($this->sessionName,$sid);
                    $this->sessionId = $sid;
                }else{
                    $this->sessionId = $cookie;
                }
            }

            $this->sessionData = $this->sessionHandler->read($this->sessionId);
            if(empty($this->sessionData)){
                $this->sessionData = new SplArray();
            }else{
                $data = \swoole_serialize::unpack($this->sessionData);
                if(!is_array($data)){
                    $data = [];
                }
                $this->sessionData = new SplArray($data);
            }
            $this->isStart = true;
            return true;
        }
    }

    private function generateSid(){
        return md5(microtime().Random::randStr(3));
    }

    function set(string $key,$val):bool
    {
        if($this->isStart){
            $this->sessionData->set($key,$val);
            return true;
        }else{
            return false;
        }
    }

    function get(string $key)
    {
        if($this->isStart){
            return $this->sessionData->get($key);
        }else{
            return null;
        }
    }

    function destroy()
    {
        if($this->isStart){
            $this->sessionHandler->destroy($this->sessionId);
            $this->sessionHandler->close();
            $this->isStart = false;
            return true;
        }else{
            return false;
        }
    }

    function close():bool
    {
        if($this->isStart){
            $this->isStart = false;
            $this->sessionHandler->write($this->sessionId,\swoole_serialize::pack($this->sessionData->getArrayCopy()));
            $this->sessionHandler->close();
            return true;
        }else{
            return false;
        }
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }
}