<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;

use App\Utility\Redis;
use Core\Swoole\AsyncTaskManager;

class Index extends BaseController {
    public function index() {
        $this->smartyDisplay("websocket_client.html");
    }
}