<?php
/**
 * Created by PhpStorm.
 * User: Cheng Yang
 * Date: 2017/9/20
 * Time: 9:36
 */
namespace App\HttpController\Api;

use App\HttpController\Api\BaseController;
use App\Utility\Utils;

/**
 * Class Upload
 * @package App\Controller\Api
 */

class Upload extends BaseController
{
    /*上传最大10M */
    const MAX_SIZE = 10485760;

    /**
     * 允许上传的文件类型
     * 可以选择的主流文件类型: text/csv
     * @var array
     */
    private $allowTypes = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'application/pdf' => 'pdf',
        'text/csv' => 'csv',
        'text/plain' => 'txt',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', //word
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx' //excel2016
    ];

    /*外网访问路径*/
    const URL = "http://path.com";

    /*绝对路径*/
    const ABSOLUTE_DIR = "/path/to/dir/";

    /*隐私文件路径 TODO 上传到对应私有路径下*/
    const PRIVATE_DIR = "/path/to/private_dir/";

    function index()
    {
        // TODO: Implement index() method.
    }

    function upload(){
        $files = $this->request()->getUploadedFiles();
        $uploadArr = [];
        if(Utils::isExist($files)){
            /* @var $file \EasySwoole\Core\Http\Message\UploadFile */
            foreach ($files as $k => $file){
                /*如果上传文件类型是允许的类型*/
                if(isset($this->allowTypes[$file->getClientMediaType()])){
                    /*文件大小小于等于最大值*/
                    if($file->getSize() <= self::MAX_SIZE){
                        $tmpDir = date('Y-m-d');
                        $tmpFile = Utils::randomStr(64, "words") . "." . $this->allowTypes[$file->getClientMediaType()];
                        $dir = self::ABSOLUTE_DIR . $tmpDir;
//                      /*如果没有此目录，则生成目录*/
                        if(!is_dir($dir)){
                            mkdir($dir);
                        }
                        $res = $file->moveTo($dir.'/'.$tmpFile);
                        if($res === true){
                            $uploadArr[$k] = self::URL.$tmpDir.'/'.$tmpFile;
                        }
                    }
                }
            }
        }
        $this->writeJson($uploadArr);
    }

}