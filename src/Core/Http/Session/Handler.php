<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/7
 * Time: 上午11:35
 */

namespace EasySwoole\Core\Http\Session;


use EasySwoole\Core\Component\Spl\SplFileStream;
use EasySwoole\Core\Utility\File;

class Handler implements \SessionHandlerInterface
{
    protected $sessionName;
    protected $savePath;
    protected $fileStream;
    protected $saveFile;

    public function close()
    {
        // TODO: Implement close() method.
        if($this->fileStream instanceof SplFileStream){
            if($this->fileStream->getStreamResource()){
                $this->fileStream->unlock();
            }
            $this->fileStream = null;
            return true;
        }else{
            return true;
        }
    }

    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
        $this->close();
        if(file_exists($this->saveFile)){
            unlink($this->saveFile);
        }
        return true;
    }

    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
        $current = time();
        $res = File::scanDir($this->savePath);
        if(is_array($res)){
            foreach ($res as $file){
                $time = fileatime($file);
                if($current - $time > $maxlifetime){
                    unlink($file);
                }
            }
        }
    }

    public function open($save_path, $name):bool
    {
        // TODO: Implement open() method.
        $this->savePath = $save_path;
        $this->sessionName = $name;
        return true;
    }

    public function read($session_id):string
    {
        // TODO: Implement read() method.
        if(!$this->fileStream){
            $this->saveFile = $this->savePath."/{$this->sessionName}_{$session_id}";
            $this->fileStream = new SplFileStream($this->saveFile);
        }
        if(!$this->fileStream->getStreamResource()){
            return '';
        }else{
            //此处即实现了锁阻塞
            $this->fileStream->lock();
            return  $this->fileStream->__toString();
        }
    }

    public function write($session_id, $session_data):bool
    {
        // TODO: Implement write() method.
        if(!$this->fileStream->getStreamResource()){
            return false;
        }else{
            $this->fileStream->truncate();
            $this->fileStream->rewind();
            return $this->fileStream->write($session_data);
        }
    }
}