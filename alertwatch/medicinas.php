<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Medicinas - AlertWatch</title>
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
    <a href="medicinas.php"><button class="active">Medicinas</button></a>
    <a href="historial.php"><button>Historial</button></a>
    <a href="config.php"><button >Config</button></a>
</nav>


    <main>
        <div class="reminders-header">
            <h3>Recordatorios</h3>
            <a href="agregar.php"><button class="add-btn">+ Agregar</button></a>
        </div>

        <div id="reminderList" class="reminder-card">
            <p class="no-reminders">
                <span class="calendar-icon">📅</span><br>
                No hay recordatorios configurados<br>
                <small>Agrega tu primer recordatorio</small>
            </p>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>
