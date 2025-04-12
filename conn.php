<?php
$DB_SERVER = 'localhost'; 
$DB_USERNAME = 'normal';
$DB_PASSWORD = 'password'; 
$DB_DATABASE = 'datatrain';   

try {
        $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
        } 
}
 catch (\Throwable $th) {
    echo $th;
}
?>
