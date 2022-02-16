<?php
    class Customer{
        // class for management customers data

        // declare private variables with connection parameters
        private $conn;
        private $table_name = "wgs_customers";

        // public customer proprieties
        public $id;
        public $company;
        public $companyState;
        public $companyPhone;
        public $refEmail;
        public $refName;

        // constructor
        public function __construct($db){
            $this->conn = $db;
        }

        // function to read customers (GET method only)
        function readCustomers($queryParams){
            /*  
                case 1: read all customers (if id is not set)
                case 2: read specific customer by id
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

        // function to create new customer (POST method only)
        function createCustomer(){  
            // query for insert record
            $query = "INSERT INTO " . $this->table_name . " SET
                company=:company, company_state=:company_state, company_phone=:company_phone, ref_email=:ref_email, ref_name=:ref_name";
            // prepare query
            $stmt = $this->conn->prepare($query);

            // sanitize data
            $this->company=htmlspecialchars(strip_tags($this->company));
            $this->companyState=htmlspecialchars(strip_tags($this->companyState));
            $this->companyPhone=htmlspecialchars(strip_tags($this->companyPhone));
            $this->refEmail=htmlspecialchars(strip_tags($this->refEmail));
            $this->refName=htmlspecialchars(strip_tags($this->refName));

            // bind placeholers with value
            $stmt->bindParam(":company", $this->company, PDO::PARAM_STR);
            $stmt->bindParam(":company_state", $this->companyState, PDO::PARAM_STR);
            $stmt->bindParam(":company_phone", $this->companyPhone, PDO::PARAM_STR);
            $stmt->bindParam(":ref_email", $this->refEmail, PDO::PARAM_STR);
            $stmt->bindParam(":ref_name", $this->refName, PDO::PARAM_STR);

            // execute query
            if($stmt->execute()){
                return true;
            }          
            return false;
        }

        /// function to edit existing customer (by id and PUT method only)
        function updateCustomer(){
            // query for update record
            $query = "UPDATE " . $this->table_name . " SET
                company=:company, company_state=:company_state, company_phone=:company_phone, ref_email=:ref_email, ref_name=:ref_name WHERE id=:id";
            // prepare query
            $stmt = $this->conn->prepare($query); 
            
            // sanitize data
            $this->company=htmlspecialchars(strip_tags($this->company));
            $this->companyState=htmlspecialchars(strip_tags($this->companyState));
            $this->companyPhone=htmlspecialchars(strip_tags($this->companyPhone));
            $this->refEmail=htmlspecialchars(strip_tags($this->refEmail));
            $this->refName=htmlspecialchars(strip_tags($this->refName));
            $this->id=htmlspecialchars(strip_tags($this->id));
            
            // bind placeholers with value
            $stmt->bindParam(":company", $this->company, PDO::PARAM_STR);
            $stmt->bindParam(":company_state", $this->companyState, PDO::PARAM_STR);
            $stmt->bindParam(":company_phone", $this->companyPhone, PDO::PARAM_STR);
            $stmt->bindParam(":ref_email", $this->refEmail, PDO::PARAM_STR);
            $stmt->bindParam(":ref_name", $this->refName, PDO::PARAM_STR);
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            
            // execute query
            if($stmt->execute()){
                return true;
            }          
            return false;
        }

        // function for delete existing customer (by id)
        function removeCustomer(){
            // query for remove customer
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            echo $query;
            // prepare query
            $stmt = $this->conn->prepare($query);        
            // sanitize
            $this->id=htmlspecialchars(strip_tags($this->id));
            // bind id of record to delete
            $stmt->bindParam(":id", $this->id);        
            // execute query
            if($stmt->execute()){
                return true;
            }
            return false;
        }
    }
?>