<?php
session_start();

// Guardar configuración si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['config'] = [
        'contacto' => $_POST['contacto'] ?? '',
        'min_bpm'  => $_POST['min_bpm'] ?? 50,
        'max_bpm'  => $_POST['max_bpm'] ?? 100,
    ];
    header("Location: config.php");
    exit;
}

// Cargar valores actuales
$config = $_SESSION['config'] ?? ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - AlertWatch</title>
    <link rel="stylesheet" href="style.css">
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

        <!-- Configuración de contacto y umbrales -->
        <div class="card">
            <form method="POST">
                <label for="contacto">Contacto de Emergencia</label>
                <div style="display: flex; gap: 10px;">
                    <input type="tel" name="contacto" id="contacto" placeholder="+1234567890" value="<?= $config['contacto'] ?>" required>
                    <button type="button" disabled>📞</button>
                </div>

                <h4>Umbrales de Alerta</h4>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="min_bpm" placeholder="Mínimo BPM" value="<?= $config['min_bpm'] ?>" required>
                    <input type="number" name="max_bpm" placeholder="Máximo BPM" value="<?= $config['max_bpm'] ?>" required>
                </div>

                <button type="submit" class="submit-btn">Guardar Configuración</button>
            </form>
        </div>

        <!-- Información de la app -->
        <div class="card" style="margin-top: 20px;">
            <h4>Información de la App</h4>
            <p><strong>Versión:</strong> 1.5.2</p>
            <p><strong>Dispositivo:</strong> No Conectado</p>
            <p><strong>Estado:</strong> <span style="color: red;">Inactivo</span></p>
        </div>
    </main>
</div>
</body>
</html>
