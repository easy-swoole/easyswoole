<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:08
 */

namespace EasySwoole\Core\Utility\Validate;


use EasySwoole\Core\Component\Spl\SplBean;

class ErrorBean extends SplBean
{
    protected $field;
    protected $message;
    protected $data;
    protected $failRule;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getFailRule()
    {
        return $this->failRule;
    }

    /**
     * @param mixed $failRule
     */
    public function setFailRule($failRule)
    {
        $this->failRule = $failRule;
    }

}