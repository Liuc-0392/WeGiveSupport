<?php
    //set the headers
    header("Access-Control-Allow-Origin: https://wegivesupport.net/");  //
    header('Content-Type: application/json; charset=UTF-8');            // content type and charset
    header('Access-Control-Allow-Methods: GET, POST');                  // allow only GET and POST http methods

    

    //header('WWW-Authenticate: Basic realm="WeGiveSupport"');    // send header to require user authentication



    /*if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="WeGiveSupport');
        header('HTTP/1.0 401 Unauthorized');
    } else 
    {
        echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
        echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
    }

    
















    print_r($_SERVER);
    //print_r($HTTP_SERVER_VARS);*/

    /*if($_SERVER['HTTP_AUTHORIZATION'])
    {
        echo "Authenticate header has received from client";
        header('HTTP 1.1 200 Ok');
    }
    else
        header('HTTP 1.1 401 Unauthorized');*/

    echo "\nDocumento segreto sotto login";    
?>