<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "alert";



$conn = new mysqli($host, $user, $password, $dbname); // <--- Agrega $port aquí
if ($conn->connect_error) {
    // ...
}
?>