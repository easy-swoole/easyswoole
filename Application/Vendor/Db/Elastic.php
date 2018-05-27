<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2017/11/8
 * Time: 23:25
 */
namespace App\Vendor\Db;
use Elasticsearch\ClientBuilder;

class Elastic
{
    /**
     * @var \Elasticsearch\Client; es和服务的连接
     */
    private $client;

    function __construct($conf)
    {
        $this->connect($conf);
    }

    function connect($conf){
        $this->client = ClientBuilder::create()->setHosts($conf)->build();
    }

    /**
     * @return \Elasticsearch\Client
     */
    function getClient(){
        return $this->client;
    }

}