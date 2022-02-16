<?php
    /** that's controller for tickets API calls **/
    // set the headers
    header("Access-Control-Allow-Origin: https://wegivesupport.net/");  // same-Origin Policy (anti XSS)
    header('Content-Type: application/json; charset=UTF-8');            // tell to the client the MIME and charset
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');     // allow only GET and POST http methods
    // include the needed config files
    include_once '../config/database.php';
    include_once '../objects/ticket.php';
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
            $ticket = new Ticket($db);                       
            // retreive the query parameters from url passing value and sanitize them
            $queryParams = array(
                'id'        => isset($_GET['id'])       ? htmlspecialchars(strip_tags($_GET['id']))         : '',
                'priority'  => isset($_GET['priority']) ? htmlspecialchars(strip_tags($_GET['priority']))   : '',
                'status'    => isset($_GET['status'])   ? htmlspecialchars(strip_tags($_GET['status']))     : '',
                'customer'  => isset($_GET['customer']) ? htmlspecialchars(strip_tags($_GET['customer']))   : '',
                'agent'     => isset($_GET['agent'])    ? htmlspecialchars(strip_tags($_GET['agent']))      : ''
            );
            // get the request method
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            // if is GET -> read tickets
            if($requestMethod == 'GET'){
                // call readTickets
                $stmt = $ticket->readTickets($queryParams);
                // get the record found count
                $num = $stmt->rowCount();
                // if more than 0 record found
                if($num>0){                
                    // initializate tickets array
                    $tickets_arr["records"]=array();
                    // retrieve the content
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        // array that contains all data of each ticket found
                        $ticket_item=array(
                            "id" => $row['id'],
                            "opening_date" => $row['opening_date'],
                            "closing_date" => $row['closing_date'],
                            "customer" => $row['customer'],
                            "agent" => $row['agent'],
                            "priority" => $row['priority'],
                            "status" => $row['status'],
                            "object" => $row['object'],
                            "message" => $row['message'],
                            "direct link" => "/api/tickets/".$row['id']
                        );
                        array_push($tickets_arr["records"], $ticket_item);
                    }                
                    // set response code 200 OK
                    http_response_code(200);                
                    // show tickets data in json format
                    echo json_encode($tickets_arr);
                }
                // if no tickets found
                else{  
                    // set response code 404 Not found
                    http_response_code(404);                  
                    // tell the user no tickets found
                    echo json_encode(
                        array("message" => "No tickets found.")
                    );
                }
            // if is PUT -> update ticket
            }elseif($requestMethod == 'PUT'){
                // retreive posted data
                $data = json_decode(file_get_contents("php://input"));

                // check if the request is getting from tickets/<id> endpoint and not /tickets only
                if(!empty($queryParams['id']) &&
                        !empty($data->customer) &&
                            !empty($data->agent) &&
                                !empty($data->priority) &&
                                    !empty($data->object)){
                    // set ticket property values
                    $ticket->id = $queryParams['id'];
                    $ticket->openingDate = $data->opening_date;
                    $ticket->closingDate = $data->closing_date;
                    $ticket->customer = $data->customer;
                    $ticket->agent = $data->agent;
                    $ticket->priority = $data->priority;
                    $ticket->status = $data->status;
                    $ticket->object = $data->object;
                    $ticket->message = $data->message;

                    // update the ticket
                    if($ticket->updateTicket()){                
                        // set response code 200 OK
                        http_response_code(200);                
                        // tell the user
                        echo json_encode(array("message" => "Ticket was updated."));
                    }                
                    // if unable to update the ticket, tell the user
                    else{                
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to update ticket."));
                    }
                }
                else{
                    // set response code 400 Bad request
                    http_response_code(400);
                    // tell the user
                    echo json_encode(array("message" => "Unable to update ticket. Data is incomplete or endpoint isn't correct for requested operation."));
                }
            // if is POST -> create ticket
            }elseif($requestMethod == 'POST'){
                // retreive posted data
                $data = json_decode(file_get_contents("php://input"));
                // make sure main data are not empty
                if($_SERVER['REQUEST_URI'] == "/api/tickets" &&
                        !empty($data->customer) &&
                            !empty($data->agent) &&
                                !empty($data->priority) &&
                                    !empty($data->object)){
                    // set ticket property values
                    $ticket->openingDate = date('Y-m-d H:i:s'); // now
                    $ticket->closingDate = NULL;                // NULL for now
                    $ticket->customer = $data->customer;
                    $ticket->agent = $data->agent;
                    $ticket->priority = $data->priority;
                    $ticket->status = '1';                      // obviously open
                    $ticket->object = $data->object;
                    $ticket->message = $data->message;

                    // create the ticket
                    if($ticket->createTicket()){                
                        // set response code 201 Created
                        http_response_code(201);                
                        // tell the user
                        echo json_encode(array("message" => "Ticket was created."));
                    }                
                    // if unable to create the ticket, tell the user
                    else{                
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to create ticket."));
                    }
                }                
                // tell the user which data is incomplete
                else{                
                    // set response code 400 Bad request
                    http_response_code(400);                
                    // tell the user
                    echo json_encode(array("message" => "Unable to create ticket. Data is incomplete or endpoint isn't correct for requested operation."));
                }
            // if is DELETE -> delete ticket
            }elseif($requestMethod == 'DELETE'){

                if(!empty($queryParams['id'])){
                    $ticket->id = $queryParams['id'];
                    
                    if($ticket->removeTicket()){
                        // set response code 200 OK
                        http_response_code(201);                
                        // tell the user
                        echo json_encode(array("message" => "Ticket was removed."));
                    }
                    else{
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to remove ticket."));
                    }
                }
                else{
                    // set response code 400 Bad request
                    http_response_code(400);                
                    // tell the user
                    echo json_encode(array("message" => "Unable to remove ticket. Data is incomplete or endpoint isn't correct for requested operation."));
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