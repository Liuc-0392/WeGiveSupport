<?
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
        public $email;

        // constructor
        public function __construct($db)
        {
            $this->conn = $db;
        }

        // function to check if username exists
        function usernameExists()
        {
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
        
                // return true because username exists in the database
                return true;
            }        
            // return false if username does not exist in the database
            return false;
        }
    }
?>