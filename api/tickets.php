<?php
    /** that's controller for tickets API calls **/

    // set the headers
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
            // get database connection
            $database = new Database();
            $db = $database->getConnection();
            // instantiate new ticket object
            $ticket = new Ticket($db);
            // get the request method
            $requestMethod = $_SERVER["REQUEST_METHOD"];            
            // retreive the query parameters from url passing value and sanitize them
            $queryParams = array(
                'id'        => isset($_GET['id'])       ? htmlspecialchars(strip_tags($_GET['id']))         : '',
                'priority'  => isset($_GET['priority']) ? htmlspecialchars(strip_tags($_GET['priority']))   : '',
                'status'    => isset($_GET['status'])   ? htmlspecialchars(strip_tags($_GET['status']))     : ''
            );
            // if is GET -> read tickets
            if($requestMethod == 'GET'){
                $ticket->readTickets($queryParams);
            }elseif($requestMethod == 'PUT'){

            }elseif($requestMethod == 'POST'){

            }elseif($requestMethod == 'DELETE'){
                
            }
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