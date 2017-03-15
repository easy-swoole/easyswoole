<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:51
 */

namespace Core\Utility\Validate;


class Error
{
    protected $errorMsg;
    protected $errorColumn;
    protected $errorRule;
    protected $errorData;
    function __construct($errorColumn,$errorData,$errorRule,$errorMsg)
    {
        // TODO: Implement __destruct() method.
        $this->errorColumn = $errorColumn;
        $this->errorMsg = $errorMsg;
        $this->errorRule = $errorRule;
        $this->errorData = $errorData;
    }

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @return mixed
     */
    public function getErrorColumn()
    {
        return $this->errorColumn;
    }

    /**
     * @return mixed
     */
    public function getErrorRule()
    {
        return $this->errorRule;
    }

    /**
     * @return mixed
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return (string)$this->errorMsg;
    }

}