<?php
session_start();
include 'conexion.php'; // Incluye tu archivo de conexión a la BD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Encripta la contraseña para mayor seguridad
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Verifica si el nombre de usuario ya existe
    $sql_check = "SELECT id FROM users WHERE username = '$username'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $error = "El nombre de usuario ya existe. Por favor, elige otro.";
    } else {
        // Inserta el nuevo usuario en la base de datos
        $sql_insert = "INSERT INTO users (username, password_hash) VALUES ('$username', '$password_hash')";
        if ($conn->query($sql_insert) === TRUE) {
            // Registro exitoso, redirige a la página de inicio de sesión
            header("Location: login.php?status=registered");
            exit;
        } else {
            $error = "Error al registrar el usuario: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - AlertWatch</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .auth-container h2 {
            color: #d60087;
            margin-bottom: 20px;
        }
        .auth-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .auth-container input[type="text"],
        .auth-container input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .auth-container button {
            background-color: #d60087;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .auth-container button:hover {
            background-color: #b80071;
        }
        .auth-container p {
            margin-top: 15px;
        }
        .auth-container a {
            color: #d60087;
            text-decoration: none;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
         <img src="logoalertorigi.jpeg" alt="Logo de la empresa" style="width: 140px; height: auto; margin-bottom: 20px;">
        <h2>Registro de Usuario</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form action="registro.php" method="POST">
            <input type="text" name="username" placeholder="Nombre de Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
    </div>
</body>
</html>