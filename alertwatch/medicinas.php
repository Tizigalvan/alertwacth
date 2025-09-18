<?php 
session_start();
include 'conexion.php'; 

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicamen  = mysqli_real_escape_string($conn, $_POST['medicamen']);
    $contenido  = mysqli_real_escape_string($conn, $_POST['contenido']);
    $gramos     = mysqli_real_escape_string($conn, $_POST['gramos']);
    $horario    = mysqli_real_escape_string($conn, $_POST['horario']);
    $frecuencia_nombre = mysqli_real_escape_string($conn, $_POST['frecuencia']);
    
    // Se inserta directamente en medicamentos. La clave foránea se encargará de la validación.
    $sql = "INSERT INTO medicamentos (medicamen, contenido, gramos, horario, frecuencia) 
            VALUES ('$medicamen', '$contenido', '$gramos', '$horario', '$frecuencia_nombre')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: medicinas.php"); 
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Lógica para obtener las opciones de frecuencia de la BD
$frecuencias = [];
$sql_frecuencia = "SELECT nombre FROM frecuencia ORDER BY id_fre";
$result_frecuencia = $conn->query($sql_frecuencia);
if ($result_frecuencia->num_rows > 0) {
    while($row = $result_frecuencia->fetch_assoc()) {
        $frecuencias[] = $row['nombre'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Medicinas - AlertWatch</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: red;
        }
        form label {
            display: block;
            margin-top: 10px;
        }
        form input, form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        form button {
            margin-top: 15px;
            background: #e60073;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }
        form button:hover {
            background: #cc0066;
        }
        
        /* Estilos para el carrusel */
        #reminderList {
            display: flex;
            overflow-x: auto; /* Permite el desplazamiento horizontal */
            scroll-snap-type: x mandatory; /* Para que se detenga en cada tarjeta */
            gap: 15px;
            padding: 20px 0;
            -webkit-overflow-scrolling: touch; /* Mejora el desplazamiento en dispositivos móviles */
        }
        .reminder-card {
            flex-shrink: 0; /* Evita que las tarjetas se contraigan */
            width: 300px; /* Ancho fijo para cada tarjeta del carrusel */
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: left;
            transition: transform 0.2s;
            border: 1px solid #f0f0f0;
            scroll-snap-align: start; /* Alinea cada tarjeta al inicio del scroll */
        }
        .reminder-card:hover {
            transform: translateY(-5px);
        }
        .reminders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .add-btn {
            background: #e60073;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
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
        <a href="medicinas.php"><button class="active">Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>

    <main>
        <div class="reminders-header">
            <h3>Recordatorios</h3>
            <button id="openModal" class="add-btn">+ Agregar</button>
        </div>

        <div id="reminderList">
        <?php
        $sql = "SELECT * FROM medicamentos ORDER BY id_med DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='reminder-card'>";
                echo "<h4>💊 " . htmlspecialchars($row['medicamen']) . "</h4>";
                echo "<p><strong>Contenido:</strong> " . htmlspecialchars($row['contenido']) . "</p>";
                echo "<p><strong>Gramos:</strong> " . htmlspecialchars($row['gramos']) . "</p>";
                echo "<p><strong>Horario:</strong> " . htmlspecialchars($row['horario']) . "</p>";
                echo "<p><strong>Frecuencia:</strong> " . htmlspecialchars($row['frecuencia']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<div class='reminder-card'>
                      <p class='no-reminders'>
                          <span class='calendar-icon'>📅</span><br>
                          No hay recordatorios configurados<br>
                          <small>Agrega tu primer recordatorio</small>
                      </p>
                  </div>";
        }
        ?>
        </div>
    </main>
</div>

<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3>Nuevo Recordatorio</h3>
    <form method="POST" action="">
        <label>Nombre del Medicamento</label>
        <input type="text" name="medicamen" placeholder="Ej: Aspirina" required>

        <label>Contenido</label>
        <input type="text" name="contenido" placeholder="Ej: Tabletas" required>

        <label>Gramos</label>
        <input type="text" name="gramos" placeholder="Ej: 500mg" required>

        <label>Hora</label>
        <input type="time" name="horario" required>

        <label>Frecuencia</label>
        <select name="frecuencia">
            <?php foreach ($frecuencias as $frec) : ?>
                <option value="<?php echo htmlspecialchars($frec); ?>">
                    <?php echo htmlspecialchars($frec); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Agregar Recordatorio</button>
    </form>
  </div>
</div>

<script>
// Obtener elementos
var modal = document.getElementById("myModal");
var btn = document.getElementById("openModal");
var span = document.getElementsByClassName("close")[0];

// Abrir modal
btn.onclick = function() {
  modal.style.display = "block";
}

// Cerrar modal
span.onclick = function() {
  modal.style.display = "none";
}

// Cerrar si se hace clic fuera del modal
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>

</body>
</html>