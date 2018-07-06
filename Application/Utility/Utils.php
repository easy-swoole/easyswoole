<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2017/12/3
 * Time: 11:48
 */
namespace App\Utility;

class Utils
{
    /**
     * 生成任何位数的随机字符串, type=normal 字母数字, runes 字母和数字和特殊符号
     * @param $length
     * @param string $type
     * @return bool|string
     */
    static function randomStr($length, $type="normal"){
        switch ($type){
            case "normal":
                $str = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ0123456789";
                break;
            case "runes":
                $str = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ0123456789!@#$%^&*";
                break;
            default:
                $str = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ0123456789";
                break;
        }
        /*当需要生成字符串长度小于等于字符串基础库时, 直接截取需要的长度*/
        $strlen = strlen($str);
        if($length <= $strlen){
            return substr(str_shuffle($str), 0, $length);
        }
        $coefficient = ceil($length/$strlen); //算出生成随机码长度是字符串库长度的倍数
        $randStr = "";
        for ($i = 0; $i < $coefficient; $i++){
            $randStr .= $str;
        }
        return substr(str_shuffle($randStr), 0, $length);
    }

    /**
     * 单个编码
     * 将数组里的所有元素处理一遍，把每一个字符串转为html实体以防止js注入, 一维数组
     * @param array $data
     * @return array
     */
    static function escape(array $data){
        $escapeData = array();
        foreach ($data as $k1 => $v1){
            $k = htmlspecialchars($k1, ENT_QUOTES);
            $v = htmlspecialchars($v1, ENT_QUOTES);
            $escapeData[$k] = $v;
        }
        return $escapeData;
    }

    /**
     * 批量解码
     * 将数组里的所有元素处理一遍，把每一个html实体还原, 二维数组
     * @param array $data
     * @return array
     */
    static function batchUnEscape(array $data){
        $unEscapeData = array();
        foreach ($data as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2){
                $k = htmlspecialchars_decode($k2, ENT_QUOTES);
                $v = htmlspecialchars_decode($v2, ENT_QUOTES);
                $unEscapeData[$k1][$k] = $v;
            }
        }
        return $unEscapeData;
    }

    /**
     * 单个解码
     * 将数组里的所有元素处理一遍，把每一个html实体还原, 二维数组
     * @param array $data
     * @return array
     */
    static function unEscape(array $data){
        $unEscapeData = array();
        foreach ($data as $k1 => $v1){
            $k = htmlspecialchars_decode($k1, ENT_QUOTES);
            $v = htmlspecialchars_decode($v1, ENT_QUOTES);
            $unEscapeData[$k] = $v;
        }
        return $unEscapeData;
    }

    /**
     * 只取数组中指定的字段返回新的数组
     * @param $cols array
     * @param $data array
     * @return array
     */
    static function onlyCols($cols, $data){
        $newData = [];
        foreach ($cols  as  $v){
            if(isset($data[$v])){
                $newData[$v] = $data[$v];
            }
        }
        return $newData;
    }

    /**
     * 将二维数组下面每一行的某一字段取出, 返回一维数组
     * @param $col
     * @param $data
     * @return array
     */
    static function pluck($col, &$data){
        $newData = [];
        foreach ($data  as  $v){
            if(isset($v[$col]))
            {
                $newData[] = $v[$col];
            }
        }
        return $newData;
    }

    /**
     * 将二维数组下面每一行的某一个字段取出,作为hash键整理格式如 :
     * [
     * ["id" => 1, "policy"=>2],
     * ["id" => 2, "policy"=>2],
     * ["id" => 3, "policy"=>2],
     * ]
     * 整理为
     * [
     * 1 => 2,
     * 2 => 2,
     * 3 => 2,
     * ]
     * @param string $colKey
     * @param string $colValue
     * @param $data
     * @return array
     */
    static function hashTree($colKey, $colValue, &$data){
        $newData = [];
        foreach ($data  as  $v){
            if(isset($v[$colKey]))
            {
                $newData[$v[$colKey]] = $v[$colValue];
            }
        }
        return $newData;
    }

    /**
     * 给一维数组每一个元素加入单引号， 方便wherein sql中使用
     * @param array $data
     * @return array|null
     */
    static function addQuote(array $data){
        foreach ($data as $v){
            $quoteArr[] = "'{$v}'"; //加入单引号，之后wherein语句中使用
        }
        if(empty($quoteArr)) $quoteArr= null;
        return $quoteArr;
    }

    /**
     *解析json字符串数据或者逗号，或者任意符号分隔数据
     * @param $str
     * @param string $type 解析类型: json, separate
     * @param string $separator 分隔符  如 逗号, 各类符号
     * @return array|mixed|null
     * @throws \Exception
     */
    static function parseStr(&$str, $type="json", $separator=","){
        /*json字符串类型*/
        if($type === "json"){
            $arr = @json_decode($str, true);
            if(!isset($arr) || empty($arr) || !is_array($arr)){
                throw new \Exception("Utils line:124 => json解析失败");
            }
            return $arr;
        }
        /*符号分隔类型*/
        if($type === "separate"){
            $arr = @explode($separator, $str);
            if(!isset($arr) || empty($arr) || !is_array($arr)){
                throw new \Exception("Utils line:132 => 分隔符解析失败");
            }
            return $arr;
        }
        return null;
    }

    /**
     * 校验一维数组中的值(整数,小数,数值型)
     * @param array $data
     * @param string $type int,float,string,numeric数值型
     * @return bool
     * @throws \Exception
     */
    static function checkArr(array &$data, $type="numeric"){
        if($type === "numeric"){
            foreach ($data as $k => $v){
                if(!is_numeric($v)){
                    throw new \Exception("Utils line:150 int check fail!");
                }
            }
        }
        return true;
    }

    /**
     * 二维数组根据某一键排序, 但是此二维数组的第一层不能带键，一层键必须为0,1,2,3自然序
     * @param array $data
     * @param string $key
     * @param string $sortType desc降序, asc升序
     * @return false|array
     */
    static function keySort(array &$data, $key, $sortType="desc"){
        //判断参数是否是一个数组
        if(!is_array($data)) return false;

        $length=count($data);

        if($length<=1) return $data;
        //使用for循环进行遍历，把第一个元素当做比较的对象
        for ($i=0; $i<$length; $i++) {
            /*升序或者降序排序*/
            $hot = $data[$i][$key];
            $tmp = $data[$i];
            $k = $i;
            if ($sortType === "desc") {
                for ($j = $i; $j < $length; $j++) {
                    if ($data[$j][$key] > $hot) {
                        $hot = $data[$j][$key];
                        $k = $j;
                    }
                }
                $tmp = $data[$k];
                $data[$k] = $data[$i];
                $data[$i] = $tmp;
            } else {
                for ($j = $i; $j < $length; $j++) {
                    if ($data[$j][$key] < $hot) {
                        $hot = $data[$j][$key];
                        $k = $j;
                    }
                }
                $tmp = $data[$k];
                $data[$k] = $data[$i];
                $data[$i] = $tmp;
            }
        }
    }

    /**
     * 将一维数组转为哈希数组
     * @param array $data
     * @return array
     */
    static function hashArr(array &$data){
        $arr = [];
        foreach ($data as $k => $v){
            $arr[$v] = 1;
        }
        return $arr;
    }

    /**
     * 将二维数组某一个键 转为哈希数组
     * 以某个字段作为件
     * @param array $data
     * @param $key string
     * @return array
     * @throws \Exception
     */
    static function hashKey($key, array &$data){
        $newArr = [];
        foreach ($data as $k => $v){
            if(isset($newArr[$v[$key]]) && !empty($newArr[$v[$key]])){
                throw new \Exception("Utils line 256: 字段{$key}赋值重复");
            }
            $newArr[$v[$key]] = $v;
        }
        return $newArr;
    }


    /**
     * 判断数据是否存在并且不为空
     * @param mixed $data
     * @param $type string array数组
     * @return bool
     */
    static function isExist(&$data, $type = "array"){
        if($type === "array"){
            if(isset($data) && !empty($data) && is_array($data)){
                return true;
            }
            else{
                return false;
            }
        }
        return false;
    }

    /**
     * 统一错误处理格式
     * ["errMsg" => ["单日短信发送次数达到上限", "出错xxx"]]
     * @param $error
     * @return string
     */
    static function handleError($error){
        $errorMsg = '';
        foreach ($error as $k => $v){
            foreach ($v as $msg){
                $errorMsg .= $msg . ";";
            }
        }
        return substr($errorMsg, 0, -1) . ".";
    }

    /**
     * 初始化page和pageSize参数, page从0开始pageSize默认为10, 并且返回偏移offset和pageSize
     * @param $data
     * @return array 返回偏移offset和pageSize
     */
    static function initPageAndPageSize(&$data) :array {
        if(isset($data['page']) && filter_var($data['page'], FILTER_VALIDATE_INT) && $data['page'] >= 0){
            $page = (int)$data['page'];
        }
        else{
            $page = 0;
        }
        if(isset($data['pageSize']) && filter_var($data['pageSize'], FILTER_VALIDATE_INT) && $data['pageSize'] > 0){
            $pageSize = (int)$data['pageSize'];
        }
        else{
            $pageSize = 10;
        }
        return [
            'offset' => $page*$pageSize,
            'pageSize' => $pageSize
        ];
    }

    /**
     * TODO 暂时只处理get
     * curl地址(第三方回调)
     * @param $host
     * @param $data
     * @param $method
     * @return mixed
     */
    static function curlUrl($host, array $data, $method){
        $headers = array();
        //去除末尾的/
        $last = substr($host, -1);
        if($last === "/"){
            $host = substr($host,0,-1);
        }
        $url = $host;
        if(is_array($data)){
            $url .= "?";
            foreach ($data as $key => $value){
                $url .= $key . "=" . urlencode($value) . "&";
            }
        }
        $url = substr($url, 0, -1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_NOBODY, FALSE);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $res = curl_exec($curl);
        return $res;
    }

    /**
     * 隐藏手机号中间四位
     * @param $mobile
     */
    static function hindMobile(&$mobile){
        $mobile = substr($mobile, 0, 3)."****".substr($mobile, 7);
    }

}