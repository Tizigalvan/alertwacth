<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Recordatorio</title>
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
        <button disabled>Historial</button>
        <button disabled>Config</button>
    </nav>

    <main>
        <div class="modal-content" style="margin: 50px auto; max-width: 400px;">
            <a href="medicinas.php" class="close" style="float:right;">×</a>
            <h3>Nuevo Recordatorio</h3>
            <form id="reminderForm">
                <label>Nombre del Medicamento</label>
                <input type="text" id="medName" placeholder="Ej: Aspirina" required>

                <label>Hora</label>
                <input type="time" id="medTime" required>

                <label>Frecuencia</label>
                <select id="medFreq">
                    <option value="Todos los días">Todos los días</option>
                    <option value="Cada 8 horas">Cada 8 horas</option>
                    <option value="Cada 12 horas">Cada 12 horas</option>
                </select>

                <button type="submit" class="submit-btn">Agregar Recordatorio</button>
            </form>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>
