<?php
session_start();

// Simular nuevo BPM al presionar conectar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bpm = rand(55, 100);
    $hora = date("H:i:s");

    $_SESSION['historial'][] = ['bpm' => $bpm, 'hora' => $hora];
    $_SESSION['ultimo_bpm'] = $bpm;

    header("Location: index.php");
    exit;
}

$ultimo_bpm = $_SESSION['ultimo_bpm'] ?? 72;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AlertWatch</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <header>
        <h1>❤️ AlertWatch</h1>
        <p>Tu salud cardíaca bajo control</p>
    </header>

    <nav>
        <a href="index.php"><button class="active">Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>

    <main>
        <div class="monitor-card">
            <p class="status"><span class="dot red"></span> Desconectado</p>
            <h2 class="bpm"><?= $ultimo_bpm ?></h2>
            <p class="bpm-label">BPM</p>

            <form method="POST">
                <button type="submit" class="connect-btn">Conectar</button>
            </form>
        </div>

        <div class="stats">
            <div class="stat">
                <p>Mínimo</p>
                <h3 class="green">0</h3>
            </div>
            <div class="stat">
                <p>Promedio</p>
                <h3 class="blue">0</h3>
            </div>
            <div class="stat">
                <p>Máximo</p>
                <h3 class="red">0</h3>
            </div>
        </div>
    </main>
</div>
</body>
</html>
