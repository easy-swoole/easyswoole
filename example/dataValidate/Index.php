<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/15
 * Time: 下午8:21
 */

namespace App\Controller\Api;


use Core\AbstractInterface\AbstractController;
use Core\Http\Message\Status;
use Core\Http\Message\UploadFile;
use Core\Utility\Validate\Rule;
use Core\Utility\Validate\Validate;

class Index extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        $this->response()->write("this is api index");/*  url:domain/api/index.html  domain/api/  */
    }

    function afterAction()
    {
        // TODO: Implement afterAction() method.
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
    }

    function afterResponse()
    {
        // TODO: Implement afterResponse() method.
    }

    function testValidate(){
        $validate = new Validate();

        /**
         * schools:
         * [
         *   {
         *     class:
         *     [
         *       {classNo:111}
         *     ]
         *  },
         *   {
         *     class:
         *     [
         *       {classNo:222}
         *     ]
         *   }
         * ]
         */
        //支持无限级  数组实体循环嵌套
        $validate->addColumn('schools.*.class.*.classNo')
            ->addRule(Rule::MAX_LEN, [5], '最大为5个字符');

        //当aaa=1且bbb=1的时候，ccc是必填参数
        $validate->addColumn('ccc')
            ->addRule(Rule::REQUIRED_IF, ['aaa'=>1, 'bbb'=>1], '必填参数');

        //多级属性
        $validate->addColumn('a.*.*.*.b')
            ->addRule(Rule::MAX_LEN, [5], '最大为五个字符')
            ->addRule(Rule::OPTIONAL);

        //pic下的二级属性url
        $validate->addColumn('pic.url')
            ->addRule(Rule::REQUIRED, [], '图片url是必填参数');


        $validate->addColumn('students.*.name')
            ->addRule(Rule::REQUIRED, [], '学生名字是必填参数');


        $res = $this->request()->requestParamsValidate($validate);
        if ($res->hasError()){
            $this->response()->writeJson(Status::CODE_BAD_REQUEST, $res->all());
            return;
        }

        $this->response()->writeJson(Status::CODE_OK);
    }

}