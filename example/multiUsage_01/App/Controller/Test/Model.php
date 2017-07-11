<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/8
 * Time: 上午12:45
 */

namespace App\Controller\Test;


use App\Model\Goods\Goods;
use App\Model\Goods\GoodsBean;
use App\Utility\Mysql;
use Core\Http\Message\Status;
use Core\Utility\Validate\Rule;
use Core\Utility\Validate\Validate;

class Model extends AbstractController
{
    function add(){
        /*
         * url: /test/model/add/index.html?title=xxxx&price=xxxxx
         */
        $validate = new Validate();
        $validate->addColumn("title")
            ->withErrorMsg("商品标题不能为空")->addRule(Rule::REQUIRED)
            ->addRule(Rule::MIN_LEN,array(
                5
            ),"商品标题太短");
        $validate->addColumn("price")->addRule(Rule::REQUIRED)
            ->addRule(Rule::INTEGER,array(),"价格非法");
        $ret = $this->request()->requestParamsValidate($validate);
        if(!$ret->hasError()){
            $model = new Goods();
            //SplBean会自动获取相关参数，过滤无关参数
            if($model->add(new GoodsBean($this->request()->getRequestParam()))){
                $this->response()->writeJson(200,null,"add success");
            }else{
                $this->response()->writeJson(400,null,"add fail @ ".Mysql::getInstance()->getDb()->getLastError());
            }
        }else{
            $this->response()->writeJson(Status::CODE_BAD_REQUEST,null,$ret->all());
        }
    }

    function update(){
        /*
         * url: /test/model/update/index.html?id=xxx&title=xxxx&price=xxxxx
         */
        //$id = $this->request()->getRequestParam("id");
        $validate = new Validate();
        $validate->addColumn("id")->addRule(Rule::INTEGER,array(),"商品id非法");
        $validate->addColumn("price")->addRule(Rule::OPTIONAL)
            ->addRule(Rule::INTEGER,array(),"价格非法");
        $data = $this->request()->getRequestParam(
            array(
                "id",'title','price'
            )
        );
        $ret = $validate->validate($data);
        if(!$ret->hasError()){
            $model = new Goods();
            //SplBean会自动获取相关参数，过滤无关参数
            $bean = new GoodsBean($this->request()->getRequestParam());
            if($model->update($bean->getId(),$bean->toArray())){
                $this->response()->writeJson(200,null,"update success");
            }else{
                $this->response()->writeJson(400,null,"update fail @ ".Mysql::getInstance()->getDb()->getLastError());
            }
        }else{
            $this->response()->writeJson(Status::CODE_BAD_REQUEST,null,$ret->all());
        }
    }


}