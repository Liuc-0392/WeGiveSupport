<?php
    //set the headers
    header("Access-Control-Allow-Origin: https://wegivesupport.net/");  // Same-Origin Policy (anti XSS)
    header('Access-Control-Allow-Methods: GET, POST');                  // allow only GET and POST http methods
    
    // include the needed config files
    include_once '../help/opsupport.php';

    // include JWT necessary files
    include_once '../libs/php-jwt/src/BeforeValidException.php';
    include_once '../libs/php-jwt/src/ExpiredException.php';
    include_once '../libs/php-jwt/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt/src/JWT.php';
    include_once '../libs/php-jwt/src/JWK.php';
    include_once '../libs/php-jwt/src/Key.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    // check if JWT token isn't present or is empty
    if((!isset($_COOKIE['sessionToken'])) || ($_COOKIE['sessionToken'] == "")){
        // set response code 401 Unauthorized
        http_response_code(401);
        return;
    }
    else {    
        try {
            // JWT decode
            $decoded = JWT::decode($_COOKIE['sessionToken'], new Key(OpSupport::getClaimJWT()[3], 'HS256'));    
            // set response code 200 OK
            http_response_code(200);    
        }
        catch (Exception $e){
            // if decode fails, it means jwt is invalid, so set the response code 401 Unauthorized and some details why decode fails
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            // tell the user access denied  & show error message
            echo json_encode(array(
                "message" => "Access denied.",
                "error" => $e->getMessage()
            ));
        }
    }   
?>