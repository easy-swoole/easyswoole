<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/7
 * Time: 下午5:37
 */

namespace Core\Component;


use Core\Component\Spl\SplArray;
use Core\Component\Sys\SysConst;

class ShareMemory
{
    protected $timeout = 5000;
    protected $saveFile;
    private static $instance;
    private $tempData;
    private $lockFileFp;
    private $isTransaction = false;
    private $isGetLock = false;
    /*
     * 通过文件+锁的方式来实现数据共享，建议将文件设置到/dev/shm下
     */
    function __construct()
    {
        $file = Di::getInstance()->get(SysConst::SHARE_MEMORY_FILE);
        if(empty($file)){
            $file = Di::getInstance()->get(SysConst::TEMP_DIRECTORY)."/shareMemory.men";
        }
        $this->saveFile = $file;
        if(!file_exists($this->saveFile)){
            file_put_contents($this->saveFile,'');
            file_put_contents($this->saveFile.".lock",'');
        }
    }

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function setTimeout($timeout){
        $this->timeout = $timeout;
    }

    function startTransaction(){
        if($this->isTransaction){
            return true;
        }
        if($this->lock()){
            $this->isTransaction = true;
            try{
                $data = file_get_contents($this->saveFile);
                $js = json_decode($data,1);
                $this->tempData = $js ? $js : array();
            }catch (\Exception $exception){
                $this->tempData = array();
            }
            return true;
        }else{
            trigger_error("start transaction error:get lock fail");
            return false;
        }
    }

    function commit(){
        if(!$this->isTransaction){
            return false;
        }
        $this->isTransaction = false;
        return $this->saveFile($this->tempData);
    }

    function set($key,$data){
        if($this->lock()){
            $spl = new SplArray($this->readFile());
            $spl->set($key,$data);
            return $this->saveFile($spl->getArrayCopy());
        }else{
            return false;
        }
    }

    function get($key){
        if($this->lock()){
            $spl = new SplArray($this->readFile());
            return $spl->get($key);
        }else{
            return null;
        }
    }

    function clear(){
        if($this->lock()){
            return $this->saveFile(array());
        }else{
            return false;
        }
    }

    private function readFile(){
        if($this->isTransaction){
            return $this->tempData;
        }else{
            try{
                $data = file_get_contents($this->saveFile);
                $this->unlock();
                $js = json_decode($data,1);
                return $js ? $js : array();
            }catch (\Exception $exception){
                return array();
            }
        }
    }

    private function saveFile(array $data){
        if($this->isTransaction){
            $this->tempData = $data;
            return true;
        }else{
            $ret = file_put_contents($this->saveFile,json_encode($data,1));
            $this->unlock();
            return $ret ? true : false;
        }
    }

    private function lock(){
        if($this->isGetLock){
            return true;
        }
        try{
            $this->lockFileFp = fopen($this->saveFile.".lock","c+");
            $waitTime = 0;
            while(true){
                if(!flock($this->lockFileFp,LOCK_EX|LOCK_NB)){
                    usleep(1);
                    $waitTime++;
                    if($waitTime > $this->timeout){
                        return false;
                    }
                }else{
                    $this->isGetLock = true;
                    return true;
                }
            }
        }catch (\Exception $exception){
            trigger_error($exception);
            return false;
        }
    }

    private function unlock(){
        if(!$this->isGetLock){
            return true;
        }
        try{
            $waitTime = 0;
            while(true){
                if(!flock($this->lockFileFp,LOCK_UN|LOCK_NB)){
                    usleep(1);
                    $waitTime++;
                    if($waitTime > $this->timeout){
                        return false;
                    }
                }else{
                    $this->isGetLock = false;
                    fclose($this->lockFileFp);
                    return true;
                }
            }
        }catch (\Exception $exception){
            trigger_error($exception);
            return false;
        }
    }

}