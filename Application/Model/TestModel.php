<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/5
 * Time: 22:18
 */

namespace App\Model;


class TestModel extends BaseModel
{
    function test(){
        return $this->readDb->get("test");
    }
}