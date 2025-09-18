<?php
session_start();
include 'conexion.php';

// Si el formulario fue enviado a este archivo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar los datos para evitar inyecciones SQL
    $medicamen  = mysqli_real_escape_string($conn, $_POST['medicamen']);
    $contenido  = mysqli_real_escape_string($conn, $_POST['contenido']);
    $gramos     = mysqli_real_escape_string($conn, $_POST['gramos']);
    $horario    = mysqli_real_escape_string($conn, $_POST['horario']);
    $frecuencia = mysqli_real_escape_string($conn, $_POST['frecuencia']);

    // Insertar en la base de datos
    $sql = "INSERT INTO medicamentos (medicamen, contenido, gramos, horario, frecuencia) 
             VALUES ('$medicamen', '$contenido', '$gramos', '$horario', '$frecuencia')";
    
    if ($conn->query($sql) === TRUE) {
        // Redireccionar de vuelta a la página de medicamentos
        header("Location: medicinas.php");
        exit();
    } else {
        // Mostrar un error si algo sale mal
        echo "Error: " . $conn->error;
    }
} else {
    // Si alguien intenta acceder directamente a este archivo, lo redirigimos
    header("Location: medicinas.php");
    exit();
}
?>