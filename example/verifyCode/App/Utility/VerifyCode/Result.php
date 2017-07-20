<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/23
 * Time: 11:34
 */

namespace App\Utility\VerifyCode;


class Result
{
    protected $codeStr;
    protected $imageSting;
    protected $imageMineType = 'image/png';
    function __construct($string,$image)
    {
        $this->codeStr = $string;
        $this->imageSting = $image;
    }

    /**
     * @return mixed
     */
    public function getCodeStr()
    {
        return $this->codeStr;
    }

    /**
     * @return mixed
     */
    public function getImageSting()
    {
        return $this->imageSting;
    }

    /**
     * @return string
     */
    public function getImageMineType()
    {
        return $this->imageMineType;
    }

}