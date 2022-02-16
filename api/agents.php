<?php
    /** that's controller for agents API calls **/
    // set the headers
    header("Access-Control-Allow-Origin: https://wegivesupport.net/");  // same-Origin Policy (anti XSS)
    header('Content-Type: application/json; charset=UTF-8');            // tell to the client the MIME and charset
    header('Access-Control-Allow-Methods: GET, PUT');                   // allow only GET and POST http methods
    // include the needed config files
    include_once '../config/database.php';
    include_once '../objects/agent.php';
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
    else{    
        try{
            // JWT decode
            $decoded = JWT::decode($_COOKIE['sessionToken'], new Key(OpSupport::getClaimJWT()[3], 'HS256'));    
            // get database connection
            $database = new Database();
            $db = $database->getConnection();
            // instantiate new ticket object
            $agent = new Agent($db);                       
            // retreive the query parameters from url passing value and sanitize them
            $queryParams = array(
                'id'        => isset($_GET['id'])       ? htmlspecialchars(strip_tags($_GET['id']))         : '',
                'priority'  => isset($_GET['priority']) ? htmlspecialchars(strip_tags($_GET['priority']))   : '',
                'status'    => isset($_GET['status'])   ? htmlspecialchars(strip_tags($_GET['status']))     : ''
            );
            // get the request method
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            // if is GET -> read agents
            if($requestMethod == 'GET'){
                // call readAgents
                $stmt = $agent->readAgents($queryParams);
                // get the record found count
                $num = $stmt->rowCount();
                // if more than 0 record found
                if($num>0){                
                    // initializate agents array
                    $agents_arr["records"]=array();
                    // retrieve the content
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        // array that contains all data of each agent found
                        $agent_item=array(
                            "id" => $row['id'],
                            "agent_name" => $row['agent_name'],
                            "username" => $row['username'],
                            "email" => $row['email'],
                            "direct link" => "/api/agents/".$row['id']
                        );
                        array_push($agents_arr["records"], $agent_item);
                    }
                    // set response code 200 OK
                    http_response_code(200);
                    // show agents data in json format
                    echo json_encode($agents_arr);
                }
                // if no agents found
                else{  
                    // set response code 404 Not found
                    http_response_code(404);                  
                    // tell the user no agents found
                    echo json_encode(
                        array("message" => "No agent found.")
                    );
                }
            // if is PUT -> update agent
            }elseif($requestMethod == 'PUT'){
                // retreive posted data
                $data = json_decode(file_get_contents("php://input"));

                // check if the request is getting from agents/<id> endpoint and not /agents only
                if(!empty($queryParams['id']) &&
                        !empty($data->agent_name) &&
                            !empty($data->username) &&
                                !empty($data->email)){
                    // set agent property values
                    $agent->id = $queryParams['id'];
                    $agent->agentName = $data->agent_name;
                    $agent->username = $data->username;
                    $agent->email = $data->email;            

                    // update the agent
                    if($agent->updateAgent()){                
                        // set response code 200 OK
                        http_response_code(200);                
                        // tell the user
                        echo json_encode(array("message" => "Agent was updated."));
                    }                
                    // if unable to update the ticket, tell the user
                    else{                
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to update agent."));
                    }
                }
                else{
                    // set response code 400 Bad request
                    http_response_code(400);
                    // tell the user
                    echo json_encode(array("message" => "Unable to update agent. Data is incomplete or endpoint isn't correct for requested operation."));
                }
            }
            // if is not one of previous declared accepted methods, tell the user
            else{
                // set response code 400 Bad request
                http_response_code(400);                
                // tell the user
                echo json_encode(array("message" => "Method not supported by API."));
            }
        }
        catch (Exception $e){
            // if decode fails, it means jwt is invalid, so set the response code 401 Unauthorized and some details why decode fails
            http_response_code(401);
            // tell the user access denied  & show error message
            echo json_encode(array(
                "message" => "Access denied.",
                "error" => $e->getMessage()
            ));
        }
    }
?>