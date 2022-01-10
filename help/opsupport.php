<?php
    class OpSupport{
        // class which contains methods that will used like support for other functions more important

        // function to establish if a string(haystack) begins with given substring(needle)
        public static function startsWith( $haystack, $needle ){
            $length = strlen( $needle );
            return substr( $haystack, 0, $length ) === $needle;
        }

        // function to get the information (claim) for generate the JWT
        public static function getClaimJWT(){
            // variables used for jwt
            $jwtSecret = "f391ed843821c074937696767bc811589dedc94fc1d038f475b783d5bf1f6aa4ff3d9776bd128246936fe6d4852683e19451bb288338fb6275de088d5ebce081";    // JWT encryption key
            $issuedAt = time();                                                                                                                                 // JWT generation time
            $expirationTime = $issuedAt + (60 * 60);                                                                                                            // JWT expiration (valid for 1 hour)
            $issuer = "https://wegivesupport.net";                                                                                                              // JWT issuer

            // create an array which contains the claim for JWT generation
            $claimJWT = array( $issuedAt, $expirationTime, $issuer, $jwtSecret );
            return $claimJWT;
        }
    }    
?>