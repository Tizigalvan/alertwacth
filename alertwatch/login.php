<?php
session_start();
include 'conexion.php'; // Incluye tu archivo de conexión a la BD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Busca al usuario en la base de datos
    $sql = "SELECT id, password_hash FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifica la contraseña
        if (password_verify($password, $user['password_hash'])) {
            // Inicio de sesión exitoso, establece las variables de sesión y redirige
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location:index.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Nombre de usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - AlertWatch</title>
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
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
            <p style="color: green;">¡Registro exitoso! Ahora puedes iniciar sesión.</p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Nombre de Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>
        <p>¿No tienes una cuenta? <a href="registro.php">Registrarse</a></p>
    </div>
</body>
</html>