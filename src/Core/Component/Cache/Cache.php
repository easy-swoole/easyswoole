<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/18
 * Time: 下午12:17
 */

namespace EasySwoole\Core\Component\Cache;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Memory\ChannelManager;
use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Random;
use Swoole\Table;

class Cache
{
    use Singleton;
    private $processNum;
    function __construct()
    {
        TableManager::getInstance()->add('process_cache_buff',[
            'data'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>1024*63
            ],
            'time'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>10
            ]
        ]);
        $num = intval(Config::getInstance()->getConf("EASY_CACHE.PROCESS_NUM"));
        if($num <= 0){
            $num = 1;
        }
        $this->processNum = $num;
        for ($i=0;$i<$num;$i++){
            $processName = "process_cache_{$i}";
            ChannelManager::getInstance()->add($processName);
            ProcessManager::getInstance()->addProcess(CacheProcess::class,false,$processName);
        }

    }

    private function keyToProcessNum($key):int
    {
        return base_convert( md5( $key,true ), 16, 10 )%3;
    }

    /*
     * 默认等待100ms的调度
     */
    public function get($key,$timeOut = 100)
    {
        $num = $this->keyToProcessNum($key);
        if(ServerManager::getInstance()->isStart()){
            $token = Random::randStr(8);
            ChannelManager::getInstance()->get("process_cache_{$num}")->push([
                'command'=>'get',
                'args'=>[
                    'key'=>$key,
                    'token'=>$token
                ]
            ]);
            $wait = 0;
            $table = TableManager::getInstance()->get('process_cache_buff');
            while (1){
                if($wait > $timeOut){
                    //发生超时的时候，随机执行老数据清理
                    if(mt_rand(0,9) == 1){
                        foreach ($table as $key => $item){
                            if($item['time'] > 2){
                                $table->del($key);
                            }
                        }
                    }
                    return null;
                }else{
                    if($table->exist($token)){
                        $data = $table->get($token);
                        $table->del($token);
                        return \swoole_serialize::unpack($data['data']);
                    }
                }
                usleep(1000);
                $wait++;
            }
        }else{
            //为单元测试服务
            $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$num}.data";
            if(file_exists($file)){
                $content = file_get_contents($file);
                $data = \swoole_serialize::unpack($content);
                if(isset($data[$key])){
                    return $data[$key];
                }else{
                    return null;
                }
            }else{
                return null;
            }
        }
    }

    public function set($key,$data)
    {
        $num = $this->keyToProcessNum($key);
        if(ServerManager::getInstance()->isStart()){
            ChannelManager::getInstance()->get("process_cache_{$num}")->push([
                'command'=>'set',
                'args'=>[
                    'key'=>$key,
                    'data'=>$data
                ]
            ]);
        }else{
            //为单元测试服务
            $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$num}.data";
            if(file_exists($file)){
                $content = file_get_contents($file);
                $data = \swoole_serialize::unpack($content);
            }else{
                $data[$key] = $data;
            }
            file_put_contents($file,\swoole_serialize::pack($data));
        }
    }

    function del($key)
    {
        $num = $this->keyToProcessNum($key);
        if(ServerManager::getInstance()->isStart()){
            ChannelManager::getInstance()->get("process_cache_{$num}")->push([
                'command'=>'del',
                'args'=>[
                    'key'=>$key,
                ]
            ]);
        }else{
            //为单元测试服务
            $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$num}.data";
            if(file_exists($file)){
                $content = file_get_contents($file);
                $data = \swoole_serialize::unpack($content);
                if(isset($data[$key])){
                    unset($data[$key]);
                }
            }else{
                $data = [];
            }
            file_put_contents($file,\swoole_serialize::pack($data));
        }
    }

    function flush()
    {
        for ($i=0;$i<$this->processNum;$i++){
            if(ServerManager::getInstance()->isStart()){
                ChannelManager::getInstance()->get("process_cache_{$i}")->push([
                    'command'=>'flush',
                    'args'=>[]
                ]);
            }else{
                $file = Di::getInstance()->get(SysConst::DIR_TEMP)."/process_cache_{$i}.data";
                file_put_contents($file,\swoole_serialize::pack([]));
            }
        }
    }
}