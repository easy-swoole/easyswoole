<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/30
 * Time: 下午10:59
 */

return [
    'MAIN_SERVER'=>[
        'HOST'=>'0.0.0.0',
        'PORT'=>9501,
        'SERVER_TYPE'=>\EasySwoole\Core\Swoole\ServerManager::TYPE_WEB_SERVER,
        'SOCK_TYPE'=>SWOOLE_TCP,//该配置项当为SERVER_TYPE值为TYPE_SERVER时有效
        'RUN_MODEL'=>SWOOLE_PROCESS,
        'SETTING'=>[
            'task_worker_num' => 8, //异步任务进程
            'task_max_request'=>10,
            'max_request'=>5000,//强烈建议设置此配置项
            'worker_num'=>8
        ],
    ],
    'DEBUG'=>true,
    'TEMP_DIR'=>EASYSWOOLE_ROOT.'/Temp',
    'LOG_DIR'=>EASYSWOOLE_ROOT.'/Log',
    'EASY_CACHE'=>[
        'PROCESS_NUM'=>1,//若不希望开启，则设置为0
        'PERSISTENT_TIME'=>0//如果需要定时数据落地，请设置对应的时间周期，单位为秒
    ],
    'CLUSTER'=>[
        'enable'=>false,
        'token'=>null,
        'broadcastAddress'=>['255.255.255.255:9556'],
        'listenPort'=>9556,
        'broadcastTTL'=>5,
        'serviceTTL'=>10
    ],
    "MASTER_MYSQL" => [
        'host' => '192.168.1.121',
        'username' => 'yang',
        'password' => 'yang_123',
        'db'=> 'yang_db',
        'port' => 3306,
        'charset' => 'utf8'
    ],
    "SLAVE_MYSQL" => [
        'host' => '192.168.1.122',
        'username' => 'yang',
        'password' => 'yang_123',
        'db'=> 'yang_db2',
        'port' => 3306,
        'charset' => 'utf8'
    ],
    "REDIS" => [
        "host"=>'127.0.0.1',
        "port"=>6379,
        "auth"=>'yang_123',
        "db" => 1,
    ],
    "MONGODB" => [
        "host" =>'127.0.0.1',
        "port" => 27088,
        "username" => "yang",
        "password" => "yang_123",
        "authSource" => "yang_mongo" //授权库
    ],
    "ELASTIC" => [
        [
            'host' => '127.0.0.1',
            'port' => '9200',
            'scheme' => 'http',
            'user' => 'elastic',
            'pass' => 'yangHv4y'
        ],         // IP + Port ,可以配置多个cluster
    ],
];