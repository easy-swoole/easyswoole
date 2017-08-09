<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/9
 * Time: 下午3:37
 */

namespace App\Model;


use Core\Component\Spl\SplBean;

class TaskBean extends SplBean
{
    /*
     * 仅仅做示例，curl opt 选项请自己写
     */
    protected $url;

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }
}