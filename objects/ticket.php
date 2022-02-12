<?php
    class Ticket{
        // class for management ticket data

        // declare private variables with connection parameters
        private $conn;
        private $table_name = "wgs_tickets";

        // public ticket proprieties
        public $id;
        public $openingDate;
        public $closingDate;
        public $customer;
        public $agent;
        public $priority;
        public $status;
        public $object;
        public $message;

        // constructor
        public function __construct($db){
            $this->conn = $db;
        }

        // function to read tickets (GET method only)
        function readTickets($queryParams){
            /*  
                case 1: read all tickets (if id, status and priority are not set)
                case 2: read specific tickets by status or priority
                case 3: read specific ticket by id    
            */
            // base query
            $query = "SELECT * FROM " . $this->table_name . " WHERE " . 1;

            foreach($queryParams as $key => $value){
                // create placeholder for future bindings
                $placeholder = ":" . $key;
                // build the query
                if(!empty($value)){
                    // append the parameter to base query
                    $query .= " AND " . $key . " = " . $placeholder;
                    // if current item is id, customer or agent, break and exit cycle
                    if((($key == 'id')) || 
                        (($key == 'customer')) || 
                            (($key == 'agent')))
                    break;
                }
            }
            // prepare the query
            $stmt = $this->conn->prepare($query);
            // re-read the query for identify placeholders and binding correctly. Placeholder = :<key>
            $placeholders = array();
            preg_match_all("~\:.*?(?=\s|$)~", $query, $placeholders);
            // bind each placeholder with corresponding value
            for($i = 0; $i < (sizeof($placeholders[0])); $i++){
                // extract value from queryParams array (before remove ':' for create correct index)
                $value = $queryParams[ltrim($placeholders[0][$i], ':')];
                // bind param with value
                $stmt->bindParam($placeholders[0][$i], $value, PDO::PARAM_INT);
            }
        }
        // function to insert new ticket (POST method only)

        // function to edit existing ticket (by id)

        // function to delete existing ticket (by id)

        // function to retrieve some statistics about ticket
            // case 1: opened ticket in the past month
            // case 2: average closing time
            // case 3:
    }
?>