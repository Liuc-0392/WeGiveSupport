<?php
    class Database{
        // class for database settings and methods

        // declare private variables with connection parameters
        private $host = "localhost";
        private $db_name = "DB_WeGiveSupport";
        private $db_username = "root";
        private $db_psw = "";
        private $conn;

        // get the database connection
        public function getConnection(){

            //initialize conn
            $this->conn = null;

            try
            {
                //try to connect
                $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8', $this->db_username, $this->db_psw);
            }
            catch(PDOException $e)
            {
                // catch and print the error occurs during connection
                echo "Error during connection: \n" . $e->getMessage();
            }

            // return db conn
            return $this->conn;
        }
    }
?>