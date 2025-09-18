<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo conecta correctamente a tu BD

// La lógica para guardar y editar es la misma.
// Si el formulario se envía, esta parte del código se ejecuta.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protección contra inyecciones SQL
    $contacto = mysqli_real_escape_string($conn, $_POST['contacto'] ?? '');
    $min_bpm = mysqli_real_escape_string($conn, $_POST['min_bpm'] ?? 50);
    $max_bpm = mysqli_real_escape_string($conn, $_POST['max_bpm'] ?? 100);

    // Esta consulta es la clave. Intenta insertar, si el 'id' ya existe, lo actualiza.
    $sql = "INSERT INTO configuracion (id, min_bpm, max_bpm, contacto) 
            VALUES (1, '$min_bpm', '$max_bpm', '$contacto') 
            ON DUPLICATE KEY UPDATE 
            min_bpm = VALUES(min_bpm), 
            max_bpm = VALUES(max_bpm), 
            contacto = VALUES(contacto)";

    if ($conn->query($sql) === TRUE) {
        // Redirige al usuario para evitar reenvío de formulario
        header("Location: config.php?status=success");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Lógica para cargar los datos en el formulario.
// Esto se ejecuta cada vez que la página carga, para mostrar los valores actuales.
$config = ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100];
$sql_select = "SELECT * FROM configuracion WHERE id = 1";
$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $config = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - AlertWatch</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .app-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            color: #d30069;
        }

        nav {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        nav a {
            margin: 0 10px;
        }

        nav button {
            background-color: #f4f4f4;
            color: #333;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        nav button.active {
            background-color: #d60087;
            color: white;
        }

        main {
            padding: 10px 0;
        }

        h3 {
            text-align: center;
            font-size: 20px;
            color: #d60087;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .card label {
            font-size: 14px;
            color: #555;
            display: block;
            margin-bottom: 8px;
        }

        .card input[type="tel"],
        .card input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            font-size: 14px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .card button.submit-btn {
            background-color: #d60087;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        .card button.submit-btn:hover {
            background-color: #b80071;
        }

        .card .info p {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }

        .card .info span {
            font-weight: bold;
        }

        .card .info p span {
            color: red;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .stat {
            text-align: center;
            font-size: 14px;
        }

        .stat h3 {
            font-size: 20px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="app-container">
    <header>
        <h1>❤️ AlertWatch</h1>
        <p>Tu salud cardíaca bajo control</p>
    </header>

    <nav>
        <a href="index.php"><button>Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="config.php"><button class="active">Config</button></a>
    </nav>

    <main>
        <h3>Configuración</h3>
        <div class="card">
            <form method="POST">
                <label for="contacto">Contacto de Emergencia</label>
                <input type="tel" name="contacto" id="contacto" placeholder="+1234567890" value="<?= htmlspecialchars($config['contacto']) ?>" required>
                <h4>Umbrales de Alerta</h4>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="min_bpm" placeholder="Mínimo BPM" value="<?= htmlspecialchars($config['min_bpm']) ?>" required>
                    <input type="number" name="max_bpm" placeholder="Máximo BPM" value="<?= htmlspecialchars($config['max_bpm']) ?>" required>
                </div>
                <button type="submit" class="submit-btn">Guardar Configuración</button>
            </form>
        </div>
        <div class="card">
            <h4>Información de la App</h4>
            <div class="info">
                <p><span>Versión:</span> 1.5.2</p>
                <p><span>Dispositivo:</span> No Conectado</p>
                <p><span>Estado:</span> <span>Inactivo</span></p>
            </div>
        </div>
    </main>
</div>

</body>
</html>