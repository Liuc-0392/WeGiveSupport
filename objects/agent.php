<?php
    class Agent{
        // class for managing agents data and login check

        // declare private variables with connection parameters
        private $conn;
        private $table_name = "wgs_agents";
        
        //public agent proprieties
        public $id;
        public $agentName;
        public $username;
        public $saltPassword;
        public $passwordHash;
        public $givenPassword;
        public $email;

        // constructor
        public function __construct($db){
            $this->conn = $db;
        }

        // function to read agents (GET method only)
        function readAgents($queryParams){
            /*  
                case 1: read all agents (if id is not set)
                case 2: read specific agent by id
            */
            // base query
            $query = "SELECT * FROM " . $this->table_name . " WHERE " . 1;

            if(!empty($queryParams['id'])){
                $query .= " AND id = :id";
            }            
            // prepare the query
            $stmt = $this->conn->prepare($query);
            
            // re-read the query for identify if is present a id placeholder and binding correctly.
            $placeholders = array();
            preg_match_all("~\:.*?(?=\s|$)~", $query, $placeholders);
            // if is present
            if(!empty($placeholders[0][0])){
                // extract value from queryParams array (before remove ':' for create correct key)
                // alert! bindParam requires a reference. It binds the variable to the statement, not the value
                $value = &$queryParams[ltrim($placeholders[0][0], ':')];
                // bind param with value
                $stmt->bindParam($placeholders[0][0], $value, PDO::PARAM_INT);
            }          
            // execute query
            $stmt->execute();  
            return $stmt;
        }

        // function to edit existing agent (by id and PUT method only)
        function updateAgent(){
            // query for update record
            $query = "UPDATE " . $this->table_name . " SET
                agent_name=:agent_name, username=:username, email=:email WHERE id=:id";
            // prepare query
            $stmt = $this->conn->prepare($query);          
            
            // sanitize data
            $this->agentName=htmlspecialchars(strip_tags($this->agentName));
            $this->username=htmlspecialchars(strip_tags($this->username));
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->id=htmlspecialchars(strip_tags($this->id));
            
            // bind placeholers with value
            $stmt->bindParam(":agent_name", $this->agentName, PDO::PARAM_STR);
            $stmt->bindParam(":username", $this->username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            
            // execute query
            if($stmt->execute()){
                return true;
            }          
            return false;
        }

        // function to check if username exists
        function usernameExists(){
            // query to check the esistence
            $query = "SELECT *
                    FROM " . $this->table_name . "
                    WHERE username = ?";
            // prepare the query
            $stmt = $this->conn->prepare($query);
            // sanitize the input from request
            $this->username = htmlspecialchars(strip_tags($this->username));
            // bind the username value
            $stmt->bindParam(1, $this->username, PDO::PARAM_STR);
            // execute the query
            $stmt->execute();
            // get number of rows
            $num = $stmt->rowCount();
 
            // if username exists, assign values to object properties for easy access and use
            if($num>0){

                // get record details / values
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
                // assign values to object properties
                $this->id = $row['id'];
                $this->agentName = $row['agent_name'];
                $this->saltPassword = $row['salt_password'];
                $this->passwordHash = $row['password_hash'];
                $this->email = $row['email'];
        
                // return true if username exists in the database
                return true;
            }        
            // return false if username does not exist in the database
            return false;
        }

        function passwordCheck(){
            // use password_verify() function to exceute the hash of $chain and compare it with $passwordHash
            // note that password_hash() returns the algorithm, cost and salt as part of the returned hash. 
            // therefore, all information that's needed to verify the hash is included in it.
            if(password_verify($this->givenPassword, $this->passwordHash))
                // if corrisponding
                return true;
            else
                // if not
                return false;
        }
    }
?>