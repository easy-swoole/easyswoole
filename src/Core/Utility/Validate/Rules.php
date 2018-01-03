<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:24
 */

namespace EasySwoole\Core\Utility\Validate;


class Rules
{
    protected $list = [];

    function add($filed,$errorMsg = null):Rule
    {
        $this->list[$filed] = ['rule'=>new Rule(),'errorMsg'=>$errorMsg];
        return $this->list[$filed]['rule'];
    }

    function getRuleList():array
    {
        return $this->list;
    }
}