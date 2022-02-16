<?php
    /** that's controller for customers API calls **/
    // set the headers
    header("Access-Control-Allow-Origin: https://wegivesupport.net/");  // same-Origin Policy (anti XSS)
    header('Content-Type: application/json; charset=UTF-8');            // tell to the client the MIME and charset
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');     // allow only GET and POST http methods
    // include the needed config files
    include_once '../config/database.php';
    include_once '../objects/customer.php';
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
            // instantiate new customer object
            $customer = new Customer($db);                       
            // retreive the query parameters from url passing value and sanitize them
            $queryParams = array(
                'id'        => isset($_GET['id'])       ? htmlspecialchars(strip_tags($_GET['id']))         : '',
                'priority'  => isset($_GET['priority']) ? htmlspecialchars(strip_tags($_GET['priority']))   : '',
                'status'    => isset($_GET['status'])   ? htmlspecialchars(strip_tags($_GET['status']))     : ''
            );
            // get the request method
            $requestMethod = $_SERVER["REQUEST_METHOD"];

            // if is GET -> read customers
            if($requestMethod == 'GET'){
                // call readCustomers
                $stmt = $customer->readCustomers($queryParams);
                // get the record found count
                $num = $stmt->rowCount();
                // if more than 0 record found
                if($num>0){                
                    // initializate customers array
                    $customers_arr["records"]=array();
                    // retrieve the content
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        // array that contains all data of each customer found
                        $customer_item=array(
                            "id" => $row['id'],
                            "company" => $row['company'],
                            "company_state" => $row['company_state'],
                            "company_phone" => $row['company_phone'],
                            "ref_email" => $row['ref_email'],
                            "ref_name" => $row['ref_name'],
                            "direct link" => "/api/customers/".$row['id']
                        );
                        array_push($customers_arr["records"], $customer_item);
                    }                
                    // set response code 200 OK
                    http_response_code(200);                
                    // show customer data in json format
                    echo json_encode($customers_arr);
                }
                // if no customer found
                else{  
                    // set response code 404 Not found
                    http_response_code(404);                  
                    // tell the user no customers found
                    echo json_encode(
                        array("message" => "No customers found.")
                    );
                }
            // if is PUT -> update customer
            }elseif($requestMethod == 'PUT'){
                // retreive posted data
                $data = json_decode(file_get_contents("php://input"));

                // check if the request is getting from customer/<id> endpoint and not /customer only
                if(!empty($queryParams['id']) &&
                        !empty($data->company) &&
                            !empty($data->ref_email) &&
                                !empty($data->ref_name)){
                    // set customer property values
                    $customer->id = $queryParams['id'];
                    $customer->company = $data->company;
                    $customer->companyState = $data->company_state;
                    $customer->companyPhone = $data->company_phone;
                    $customer->refEmail = $data->ref_email;
                    $customer->refName = $data->ref_name;

                    // update the customer
                    if($customer->updateCustomer()){                
                        // set response code 200 OK
                        http_response_code(200);                
                        // tell the user
                        echo json_encode(array("message" => "Customer was updated."));
                    }
                    // if unable to update the customer, tell the user
                    else{                
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to update customer."));
                    }
                }
                else{
                    // set response code 400 Bad request
                    http_response_code(400);
                    // tell the user
                    echo json_encode(array("message" => "Unable to update customer. Data is incomplete or endpoint isn't correct for requested operation."));
                }
            // if is POST -> create customer
            }elseif($requestMethod == 'POST'){
                // retreive posted data
                $data = json_decode(file_get_contents("php://input"));
                // make sure main data are not empty
                if($_SERVER['REQUEST_URI'] == "/api/customers" &&
                        !empty($data->company) &&
                            !empty($data->ref_email) &&
                                !empty($data->ref_name)){
                    // set ticket property values
                    $customer->company = $data->company;
                    $customer->companyState = $data->company_state;
                    $customer->companyPhone = $data->company_phone;
                    $customer->refEmail = $data->ref_email;
                    $customer->refName = $data->ref_name;

                    // create the ticket
                    if($customer->createCustomer()){                
                        // set response code 201 Created
                        http_response_code(201);                
                        // tell the user
                        echo json_encode(array("message" => "Customer was created."));
                    }                
                    // if unable to create the ticket, tell the user
                    else{                
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to create customer."));
                    }
                }                
                // tell the user which data is incomplete
                else{                
                    // set response code 400 Bad request
                    http_response_code(400);                
                    // tell the user
                    echo json_encode(array("message" => "Unable to create customer. Data is incomplete or endpoint isn't correct for requested operation."));
                }
            // if is DELETE -> delete customer
            }elseif($requestMethod == 'DELETE'){
                if(!empty($queryParams['id'])){
                    $customer->id = $queryParams['id'];
                    
                    if($customer->removeCustomer()){
                        // set response code 200 OK
                        http_response_code(201);                
                        // tell the user
                        echo json_encode(array("message" => "Customer was removed."));
                    }
                    else{
                        // set response code 503 Service unavailable
                        http_response_code(503);                
                        // tell the user
                        echo json_encode(array("message" => "Unable to remove customer."));
                    }
                }
                else{
                    // set response code 400 Bad request
                    http_response_code(400);                
                    // tell the user
                    echo json_encode(array("message" => "Unable to remove customer. Data is incomplete or endpoint isn't correct for requested operation."));
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