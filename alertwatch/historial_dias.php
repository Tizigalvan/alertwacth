<?php
session_start();
include 'conexion.php'; // Incluye el archivo de conexión a tu base de datos

// Consulta para obtener todos los registros del historial diario
$sql = "SELECT fecha, min_bpm, max_bpm, promedio_bpm FROM historial_diario ORDER BY fecha DESC";
$resultado = $conn->query($sql);

$historial_dias_anteriores = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $historial_dias_anteriores[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Diario - AlertWatch</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
   <style>
/* ... (Se mantienen los estilos existentes para móviles) ... */

body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
     background-color: #ffffff;
    color: #333;
}
nav button {
    /* ... otros estilos ... */
    border-radius: 12px;
}
.app-container {
    max-width: 400px;
    margin: auto;
    padding: 20px;
}

header {
    text-align: center;
    color: #d30069;
}

header h1 {
    margin-bottom: 5px;
}

nav {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
    margin-bottom: 20px;
}

nav button {
    flex: 1;
    background: #eee;
    border: none;
    padding: 10px;
    cursor: pointer;
    transition: 0.3s;
}

nav button.active,
nav button:hover {
    background: #ff5ca5;
    color: white;
}

.monitor-card {
    background: white;
    text-align: center;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.status {
    font-size: 14px;
    margin-bottom: 10px;
}

.dot {
    height: 10px;
    width: 10px;
    border-radius: 50%;
    display: inline-block;
}

.red {
    color: red;
}

.green {
    color: green;
}

.blue {
    color: blue;
}

.bpm {
    font-size: 48px;
    margin: 10px 0;
    color: #d30069;
}

.bpm-label {
    font-size: 16px;
    color: #555;
}

.connect-btn {
    background: #d30069;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
}
.scrollable {
    max-height: 200px;
    overflow-y: auto;
    background: #fff;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 0 4px rgba(0,0,0,0.1);
}

.stats {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.stat {
    background: #fff;
    border-radius: 10px;
    width: 30%;
    text-align: center;
    padding: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
.hidden {
    display: none;
}

nav button.active {
    background: #ff5ca5;
    color: white;
}

.reminders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
}

.add-btn {
    background: #d30069;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
}

.reminder-card {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    margin: 10px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
}

.calendar-icon {
    font-size: 40px;
    color: #999;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    position: relative;
}

.modal-content h3 {
    margin-top: 0;
}

.modal-content label {
    display: block;
    margin-top: 10px;
}

.modal-content input,
.modal-content select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.submit-btn {
    background: #d30069;
    color: white;
    border: none;
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
}

.close {
    position: absolute;
    right: 15px;
    top: 10px;
    cursor: pointer;
    font-size: 20px;
}

/* Nuevos estilos para el diseño de escritorio */
@media (min-width: 768px) {
    body {
        background-color: #f0f2f5;
    }

    .app-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 40px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        display: block; /* Se cambia a 'block' para quitar la cuadrícula */
    }

    header {
        text-align: center;
        margin-bottom: 20px;
    }

    nav {
        display: flex;
        flex-direction: row; /* Coloca los botones uno al lado del otro */
        justify-content: center; /* Centra los botones horizontalmente */
        margin-top: 20px;
        margin-bottom: 40px;
        border-right: none;
        padding-right: 0;
    }
    
    nav a {
        width: auto; /* Ancho automático para que no ocupen todo el espacio */
        margin: 0 10px; /* Espacio entre los botones */
    }
    
    nav button {
        width: auto;
        padding: 12px 25px;
        font-size: 16px;
    }

    main {
        padding: 0;
    }

    .monitor-card {
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        min-height: 250px;
        margin-bottom: 20px;
    }

    .bpm {
        font-size: 72px;
        font-weight: 300;
    }
    
    .stats {
        display: flex; /* Se cambia a flex para controlar la disposición de los elementos */
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-top: 20px;
    }
    
    .stat {
        width: auto;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .whatsapp-btn {
        background: #25D366;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        margin-left: 20px;
    }
}
</style>
</head>
<body>
<div class="app-container">
    <header>
        <img src="logoalertorigi.jpeg" alt="Logo de AlertWatch" style="width: 140px; height: 140px;">
        <p>Tu salud cardíaca bajo control</p>
    </header>

    <nav>
        <a href="index.php"><button>Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="historial_dias.php"><button class="active">Historial diario</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>

    <main>
        <h3>Historial de Días Anteriores</h3>
        <div class="history-list">
            <?php if (empty($historial_dias_anteriores)): ?>
                <p style="text-align: center; color: #555;">No hay datos de días anteriores guardados. Comienza a usar el monitor para generar historial.</p>
            <?php else: ?>
                <?php foreach ($historial_dias_anteriores as $dia): ?>
                    <div class="history-entry">
                        <h4><?= htmlspecialchars($dia['fecha']) ?></h4>
                        <p><strong>Mínimo:</strong> <span class="stat-value"><?= htmlspecialchars($dia['min_bpm']) ?> BPM</span></p>
                        <p><strong>Promedio:</strong> <span class="stat-value"><?= htmlspecialchars($dia['promedio_bpm']) ?> BPM</span></p>
                        <p><strong>Máximo:</strong> <span class="stat-value"><?= htmlspecialchars($dia['max_bpm']) ?> BPM</span></p>
                     </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>