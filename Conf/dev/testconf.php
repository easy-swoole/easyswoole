<?php
/**
 * Created by PhpStorm.
 * User: azerothyangg
 * Date: 2018/10/18
 * Time: 10:51
 */
return [
    "MASTER_MYSQL" => [
        'host' => "192.168.1.189",
        'username' => 'root',
        'password' => 'root',
        'db'=> 'dev',
        'port' => 3306,
        'charset' => 'utf8',
        'timeout' => 3
    ],
    "SLAVE_MYSQL" => [
        'host' => "192.168.1.189",
        'username' => 'root',
        'password' => 'root',
        'db'=> 'dev',
        'port' => 3306,
        'charset' => 'utf8',
        'timeout' => 3
    ],
    "CO_MYSQL" => [
        'host' => "192.168.1.189",
        'user' => 'root',
        'password' => 'root',
        'database'=> 'dev',
        'port' => 3306,
        'charset' => 'utf8',
        'timeout' => 3
    ],
    "REDIS" => [
        "host"=> "127.0.0.1",
        "port"=>6379,
        "auth"=>'',
        "db" => 1,
        "timeout" => 5.0
    ],
    "MEMCACHED" => [             //如果需要开启memcached, 则可以在EasyswooleEvent里打开memcache
        "host"=>'192.168.1.189',
        "port"=>11211,
    ],
    "MONGODB" => [
        "host" =>'192.168.1.189',
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
    "TOKEN" => [
        "length" => 128 //token字符串长度
    ],
];