<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2019/1/23
 * Time: 0:02
 */

namespace App\Vendor\Http;


use EasySwoole\Core\AbstractInterface\Singleton;

class FastCurl
{
    use Singleton;

    private $curl;

    function __construct()
    {
        $this->curl = curl_init();
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return bool|string
     */
    function sendRequest(string $url, string $method, array $data = array())
    {
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            ),
        ));
        return $response = curl_exec($this->curl);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        curl_close($this->curl);
    }
}