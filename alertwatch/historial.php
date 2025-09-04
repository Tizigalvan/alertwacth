<?php
session_start();

// Obtener historial
$historial = $_SESSION['historial'] ?? [];

// Calcular estadísticas
$valores = array_column($historial, 'bpm');
$minimo = count($valores) ? min($valores) : 0;
$maximo = count($valores) ? max($valores) : 0;
$promedio = count($valores) ? round(array_sum($valores) / count($valores)) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - AlertWatch</title>
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
        <a href="historial.php"><button class="active">Historial</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>

    <main>
        <h3>Historial de Hoy</h3>

        <div class="stats">
            <div class="stat">
                <p>Mínimo Hoy</p>
                <h3 class="green"><?= $minimo ?></h3>
            </div>
            <div class="stat">
                <p>Promedio</p>
                <h3 class="blue"><?= $promedio ?></h3>
            </div>
            <div class="stat">
                <p>Máximo Hoy</p>
                <h3 class="red"><?= $maximo ?></h3>
            </div>
        </div>

        <div class="history-list">
            <h4>Lecturas Recientes</h4>
            <div class="scrollable">
                <?php foreach (array_reverse($historial) as $lectura): ?>
                    <p><?= $lectura['hora'] ?> <span style="color: green; float:right;"><?= $lectura['bpm'] ?> BPM</span></p>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
