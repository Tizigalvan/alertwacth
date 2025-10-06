<?php 
session_start();
// NOTA: Asumo que 'conexion.php' establece la conexión a la base de datos en la variable $conn
include 'conexion.php'; 

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicamen = mysqli_real_escape_string($conn, $_POST['medicamen']);
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
    $gramos = mysqli_real_escape_string($conn, $_POST['gramos']);
    $horario = mysqli_real_escape_string($conn, $_POST['horario']);
    $frecuencia_id = mysqli_real_escape_string($conn, $_POST['frecuencia']);
    
    $sql = "INSERT INTO medicamentos (medicamen, contenido, gramos, horario, frecuencia_id) 
             VALUES ('$medicamen', '$contenido', '$gramos', '$horario', '$frecuencia_id')";
    
    if ($conn->query($sql) === TRUE) {
        $conn->close();
        header("Location: medicinas.php"); 
        exit();
    } else {
        echo "Error al insertar el medicamento: " . $conn->error;
    }
}

// Lógica para obtener las opciones de frecuencia de la BD
$frecuencias = [];
$sql_frecuencia = "SELECT id_fre, nombre FROM frecuencia ORDER BY id_fre";
$result_frecuencia = $conn->query($sql_frecuencia);
if ($result_frecuencia && $result_frecuencia->num_rows > 0) {
    while($row = $result_frecuencia->fetch_assoc()) {
        $frecuencias[] = $row;
    }
}

// Lógica para obtener los recordatorios y sus horarios
$horarios_alarma = [];
$sql_recordatorios = "SELECT horario, medicamen FROM medicamentos";
$result_recordatorios = $conn->query($sql_recordatorios);
if ($result_recordatorios && $result_recordatorios->num_rows > 0) {
    while ($row = $result_recordatorios->fetch_assoc()) {
        $horarios_alarma[] = [
            'horario' => htmlspecialchars($row['horario']),
            'medicamento' => htmlspecialchars($row['medicamen'])
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Medicinas - AlertWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
/* Tu código CSS va aquí */
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background-color: #ffffff;
    color: #333;
}
nav button {
    border-radius: 8px;
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
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
@media (min-width: 768px) {
    body { background-color: #f0f2f5; }
    .app-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 40px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        display: block;
    }
    header { text-align: center; margin-bottom: 20px; }
    nav {
        display: flex;
        flex-direction: row;
        justify-content: center;
        margin-top: 20px;
        margin-bottom: 40px;
        border-right: none;
        padding-right: 0;
    }
    nav a { width: auto; margin: 0 10px; }
    nav button { width: auto; padding: 12px 25px; font-size: 16px; }
    main { padding: 0; }
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
    .bpm { font-size: 72px; font-weight: 300; }
    .stats {
        display: flex;
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
.carousel-container {
    overflow: hidden;
    position: relative;
    width: 100%;
}
.carousel-slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
}
.carousel-item {
    min-width: 100%;
    flex-shrink: 0;
}
@media (min-width: 768px) {
    .carousel-item {
        min-width: calc(100% / 3);
    }
}
.reminder-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    box-sizing: border-box;
}
.reminder-card h4 {
    font-size: 24px;
    margin-bottom: 10px;
}
.reminder-card p {
    font-size: 18px;
    margin-bottom: 5px;
}
.no-reminders {
    text-align: center;
    font-size: 18px;
    color: #888;
}
.carousel-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    z-index: 100;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    display: block;
}
.carousel-button.prev { left: 0; }
.carousel-button.next { right: 0px; }
.carousel-button.hidden { display: none; }
.modal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}
.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 40px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}
.close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: red; }
form label { display: block; margin-top: 15px; font-size: 16px; font-weight: bold; }
form input, form select {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
}
form button {
    margin-top: 25px;
    background-color: #e60073;
    color: white;
    border: none;
    padding: 12px 20px;
    width: 100%;
    border-radius: 8px;
    cursor: pointer;
}
form button:hover { background-color: #cc0066; }
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
        <a href="medicinas.php"><button class="active">Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="historial_dias.php"><button>Historial diario</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>
    <main>
        <div class="reminders-header">
            <h3>Recordatorios</h3>
            <button id="openModal" class="add-btn">+ Agregar</button>
        </div>
        <div class="carousel-container">
            <div class="carousel-slides">
                <?php
                $sql_display = "SELECT m.*, f.nombre AS frecuencia_nombre FROM medicamentos m JOIN frecuencia f ON m.frecuencia_id = f.id_fre ORDER BY m.id_med DESC";
                $result_display = $conn->query($sql_display);

                if ($result_display->num_rows > 0) {
                    while ($row = $result_display->fetch_assoc()) {
                        echo "<div class='carousel-item'>";
                        echo "<div class='reminder-card'>";
                        echo "<h4>💊 " . htmlspecialchars($row['medicamen']) . "</h4>";
                        echo "<p><strong>Contenido:</strong> " . htmlspecialchars($row['contenido']) . "</p>";
                        echo "<p><strong>Gramos:</strong> " . htmlspecialchars($row['gramos']) . "</p>";
                        echo "<p><strong>Horario:</strong> " . htmlspecialchars($row['horario']) . "</p>";
                        echo "<p><strong>Frecuencia:</strong> " . htmlspecialchars($row['frecuencia_nombre']) . "</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='carousel-item'>";
                    echo "<div class='reminder-card'>
                                 <p class='no-reminders'>
                                     <span class='calendar-icon'>📅</span><br>
                                     No hay recordatorios configurados<br>
                                     <small>Agrega tu primer recordatorio</small>
                                 </p>
                               </div>";
                    echo "</div>";
                }
                ?>
            </div>
            <button class="carousel-button prev">❮</button>
            <button class="carousel-button next">❯</button>
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
                    <option value="<?php echo htmlspecialchars($frec['id_fre']); ?>">
                        <?php echo htmlspecialchars($frec['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Agregar Recordatorio</button>
        </form>
    </div>
</div>

<script>
// Lógica para el carrusel (MANTENIDA)
const carouselContainer = document.querySelector('.carousel-container');
const slidesContainer = document.querySelector('.carousel-slides');
const slides = document.querySelectorAll('.carousel-item');
const prevButton = document.querySelector('.carousel-button.prev');
const nextButton = document.querySelector('.carousel-button.next');
const isDesktop = window.innerWidth >= 768; 
const itemsPerSlide = isDesktop ? 3 : 1; 
let currentIndex = 0;
const totalItems = slides.length;
const totalSlides = Math.ceil(totalItems / itemsPerSlide);

function updateCarousel() {
    if (slides.length === 0) return;
    const slideWidth = slides[0].offsetWidth; 
    const movementWidth = slideWidth * itemsPerSlide;
    const offset = -currentIndex * movementWidth;
    slidesContainer.style.transform = `translateX(${offset}px)`;
    prevButton.style.display = currentIndex === 0 ? 'none' : 'block';
    nextButton.style.display = currentIndex >= totalSlides - 1 ? 'none' : 'block';
}

if (totalItems > itemsPerSlide) {
    nextButton.addEventListener('click', () => {
        if (currentIndex < totalSlides - 1) {
            currentIndex++;
            updateCarousel();
        }
    });
    prevButton.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });
} else {
    prevButton.style.display = 'none';
    nextButton.style.display = 'none';
}

let startX = 0;
let isDragging = false;
let currentTranslate = 0;

carouselContainer.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].clientX;
    const style = window.getComputedStyle(slidesContainer);
    const matrix = new WebKitCSSMatrix(style.transform);
    currentTranslate = matrix.m41;
    slidesContainer.style.transition = 'none';
});

carouselContainer.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    e.preventDefault(); 
    const currentX = e.touches[0].clientX;
    const diffX = currentX - startX;
    const newTranslate = currentTranslate + diffX;
    slidesContainer.style.transform = `translateX(${newTranslate}px)`;
});

carouselContainer.addEventListener('touchend', (event) => {
    if (!isDragging) return;
    isDragging = false;
    slidesContainer.style.transition = 'transform 0.5s ease-in-out';
    const endX = event.changedTouches[0].clientX;
    const diffX = startX - endX;
    const threshold = slides[0].offsetWidth / 4;
    if (diffX > threshold && currentIndex < totalSlides - 1) {
        currentIndex++;
    } else if (diffX < -threshold && currentIndex > 0) {
        currentIndex--;
    }
    updateCarousel();
});
updateCarousel();


// Registro del Service Worker (CORREGIDO)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(registration => {
                console.log('Service Worker registrado con éxito:', registration.scope);
            })
            .catch(error => {
                console.error('Fallo el registro del Service Worker:', error);
            });
    });
}


// --- LÓGICA DE ALARMA Y NOTIFICACIÓN (CORREGIDA) ---

const horarios = <?php echo json_encode($horarios_alarma); ?>;
const audioAlarma = new Audio('despertador.mp3'); 
audioAlarma.loop = false;
let audioActivo = false;
const alarmasDisparadas = {}; // Objeto para llevar un registro de las alarmas que ya sonaron

// 1. Solicitud de permisos (automática con interacción)
function solicitarPermisos() {
    if (Notification.permission !== 'default') {
        return;
    }
    
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            console.log('Permiso de notificación concedido.');
        } else {
            console.warn('Permiso de notificación denegado.');
        }
    });
}

// 2. Activa el audio tras la primera interacción del usuario
function activarAudio() {
    if (audioActivo) return;
    audioAlarma.volume = 0;
    audioAlarma.play()
        .then(() => {
            audioActivo = true;
            audioAlarma.pause(); 
            audioAlarma.currentTime = 0;
            console.log("Audio de alarma activado por interacción del usuario.");
            
            document.removeEventListener('click', activarAudio);
            document.removeEventListener('touchstart', activarAudio);
        })
        .catch(error => {
            console.warn("Fallo al activar el audio (se necesita interacción):", error.message);
        });
}

// Escuchamos por cualquier interacción para activar el audio y solicitar permisos
document.addEventListener('click', activarAudio);
document.addEventListener('touchstart', activarAudio);
document.addEventListener('click', solicitarPermisos);

// 3. Función principal que verifica y dispara las alarmas
function verificarYDispararAlarmas() {
    const ahora = new Date();
    const horaActualStr = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');

    horarios.forEach((item, index) => {
        // La clave única para cada alarma
        const alarmaId = `${item.horario}-${item.medicamento}`; 
        
        // Convertimos el horario de la BD a un objeto Date para comparar
        const [hora, minuto] = item.horario.split(':').map(Number);
        const horarioAlarma = new Date();
        horarioAlarma.setHours(hora, minuto, 0, 0);

        // Obtenemos la hora actual sin segundos ni milisegundos para una comparación más precisa
        const ahoraSinSegundos = new Date(ahora);
        ahoraSinSegundos.setSeconds(0, 0);

        // Verificamos si la hora actual es exactamente igual a la hora de la alarma
        // y si no ha sido disparada en el último minuto
        if (horarioAlarma.getTime() === ahoraSinSegundos.getTime() && !alarmasDisparadas[alarmaId]) {
            console.log(`¡Alarma disparada para ${item.horario} - ${item.medicamento}!`);

            // Marcar la alarma como disparada para evitar repeticiones en el mismo minuto
            alarmasDisparadas[alarmaId] = true;

            // Disparar Alarma Sonora (si el audio está activo)
            if (audioActivo) {
                audioAlarma.volume = 1; 
                audioAlarma.currentTime = 0;
                audioAlarma.play().catch(error => {
                    console.error('Error al reproducir el audio de alarma:', error);
                });
            } else {
                console.warn("La alarma sonora no pudo reproducirse. Falta interacción previa.");
            }
            
            // Mostrar Alerta de JavaScript
            alert(`⏰ ¡Es hora de tomar: ${item.medicamento} a las ${item.horario}!`);
            
            // Mostrar Notificación de Escritorio (si hay permiso)
            if (Notification.permission === 'granted') {
                 new Notification('🚨 ALERTA DE MEDICAMENTO', {
                     body: `Toma ${item.medicamento} a las ${item.horario}.`,
                     icon: 'logoalertorigi.jpeg',
                     vibrate: [200, 100, 200]
                 });
            }
        }
    });
}

// Reiniciar el registro de alarmas cada minuto para la próxima ronda de verificación
setInterval(() => {
    const ahora = new Date();
    // Reiniciar el registro solo en el cambio de minuto para evitar que se reinicie
    // y se dispare la misma alarma en el mismo minuto.
    if (ahora.getSeconds() === 0) {
        Object.keys(alarmasDisparadas).forEach(key => delete alarmasDisparadas[key]);
        console.log("Registro de alarmas reiniciado.");
    }
}, 1000);

// Iniciar la verificación recurrente cada segundo
setInterval(verificarYDispararAlarmas, 1000); 

// Lógica para el modal
var modal = document.getElementById("myModal");
var btn = document.getElementById("openModal");
var span = document.getElementsByClassName("close")[0];

btn.onclick = function() {
    modal.style.display = "block";
}

span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>