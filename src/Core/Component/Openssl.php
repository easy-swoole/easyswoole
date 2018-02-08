<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/8
 * Time: 下午1:50
 */

namespace EasySwoole\Core\Component;


class Openssl
{
    private $key;
    private $method;

    function __construct($key,$method = 'DES-EDE3')
    {
        $this->key = $key;
        $this->method = $method;
    }

    public function encrypt(string $data):string
    {
        return openssl_encrypt($data,$this->method,$this->key);
    }

    public function decrypt(string $raw)
    {
        return openssl_decrypt($raw,$this->method,$this->key);
    }
}