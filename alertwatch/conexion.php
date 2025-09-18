<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "alert"; // el nombre de tu base de datos

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
