<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午11:46
 */

namespace App\Controller\Test;


use App\Model\AsyncTask;
use Core\Component\Barrier;
use Core\Component\Logger;
use Core\Swoole\AsyncTaskManager;

class Async extends AbstractController
{
    function single(){
        /*
         * url is :/test/async/single/index.html
         */
        AsyncTaskManager::getInstance()->add(AsyncTask::class);
        $this->response()->write("async task test 1");
    }

    function single2(){
        /*
       * url is :/test/async/single2/index.html
       */
        AsyncTaskManager::getInstance()->add(function (){
            Logger::console("this is async task 2");
            return "test data for async task 2";
        });
        $this->response()->write("async task test 2");
    }

    function single3(){
        /*
       * url is :/test/async/single3/index.html
       */
        AsyncTaskManager::getInstance()->add(function (){
            Logger::console("this is async task 3");
            return "test data for async task 3";
        },AsyncTaskManager::TASK_DISPATCHER_TYPE_RANDOM,function (\swoole_http_server $server, $task_id, $resultData){
            Logger::console("call back for async task 3 with data {$resultData}");
        });
        $this->response()->write("async task test 3");
    }

    function barrier(){
        /*
          * url is :/test/async/barrier/index.html
        */
        $barrier = new Barrier();
        $barrier->add("a",function (){
            usleep(50000);
            return time();
        });
        $barrier->add("b",function (){
            sleep(2);
            return time();
        });
        $barrier->add("c",function (){
            usleep(50000);
            return time();
        });
        $this->response()->write( $barrier->run(1));
    }
}