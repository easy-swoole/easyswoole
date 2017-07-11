<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/8
 * Time: 上午12:41
 */

namespace App\Model\Goods;


use Core\Component\Spl\SplBean;

class GoodsBean extends SplBean
{
    protected $id;
    protected $title;
    protected $price;
    protected $publishTime;

    protected function initialize()
    {
        // TODO: Implement initialize() method.
        $this->publishTime = time();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPublishTime()
    {
        return $this->publishTime;
    }

    /**
     * @param mixed $publishTime
     */
    public function setPublishTime($publishTime)
    {
        $this->publishTime = $publishTime;
    }


}
