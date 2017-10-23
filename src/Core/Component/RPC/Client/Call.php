<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午5:40
 */

namespace Core\Component\RPC\Client;


use Core\Component\RPC\Common\Package;

class Call
{
    protected $package;
    protected $successCall;
    protected $failCall;
    function __construct(Package $package,callable $success = null,callable $fail = null)
    {
        $this->package = $package;
        $this->successCall = $success;
        $this->failCall = $fail;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return callable
     */
    public function getSuccessCall()
    {
        return $this->successCall;
    }

    /**
     * @return callable
     */
    public function getFailCall()
    {
        return $this->failCall;
    }
}