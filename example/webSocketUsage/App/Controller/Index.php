<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use Core\AbstractInterface\AbstractController;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->response()->write(
            "
            <!DOCTYPE html>  
<meta charset=\"utf-8\" />  
<title>WebSocket Test</title>  
<script language=\"javascript\"type=\"text/javascript\">  
    var wsUri =\"ws://127.0.0.1:9501\"; 
    var output;  
    
    function init() { 
        output = document.getElementById(\"output\"); 
        testWebSocket(); 
    }  
 
    function testWebSocket() { 
        websocket = new WebSocket(wsUri); 
        websocket.onopen = function(evt) { 
            onOpen(evt) 
        }; 
        websocket.onclose = function(evt) { 
            onClose(evt) 
        }; 
        websocket.onmessage = function(evt) { 
            onMessage(evt) 
        }; 
        websocket.onerror = function(evt) { 
            onError(evt) 
        }; 
    }  
 
    function onOpen(evt) { 
        writeToScreen(\"CONNECTED\"); 
        doSend(\"WebSocket rocks\"); 
    }  
 
    function onClose(evt) { 
        writeToScreen(\"DISCONNECTED\"); 
    }  
 
    function onMessage(evt) { 
        writeToScreen('<span style=\"color: blue;\">RESPONSE: '+ evt.data+'</span>'); 
        websocket.close(); 
    }  
 
    function onError(evt) { 
        writeToScreen('<span style=\"color: red;\">ERROR:</span> '+ evt.data); 
    }  
 
    function doSend(message) { 
        writeToScreen(\"SENT: \" + message);  
        websocket.send(message); 
    }  
 
    function writeToScreen(message) { 
        var pre = document.createElement(\"p\"); 
        pre.style.wordWrap = \"break-word\"; 
        pre.innerHTML = message; 
        output.appendChild(pre); 
    }  
 
    window.addEventListener(\"load\", init, false);  
</script>  
<h2>WebSocket Test</h2>  
<div id=\"output\"></div>  
</html>
            "
        );

    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFount($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
    }

    function afterResponse()
    {
        // TODO: Implement afterResponse() method.
    }
    function test(){
        $this->response()->write("this is index test");/*  url:domain/test/index.html  domain/test/   domain/test  */
    }

}