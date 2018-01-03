<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午2:39
 */

namespace EasySwoole\Core\Utility\Validate;


class Result
{
    protected $ruleData;
    protected $errorList;
    function __construct(array $ruleData,ErrorList $errorList)
    {
        $this->ruleData = $ruleData;
        $this->errorList = $errorList;
    }

    /**
     * @return mixed
     */
    public function getRuleData()
    {
        return $this->ruleData;
    }

    /**
     * @return mixed
     */
    public function getErrorList():ErrorList
    {
        return $this->errorList;
    }

    public function hasError():bool
    {
        return $this->getErrorList()->hasError();
    }

}