<?php 
session_start();
include 'conexion.php'; 

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicamen = mysqli_real_escape_string($conn, $_POST['medicamen']);
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
    $gramos = mysqli_real_escape_string($conn, $_POST['gramos']);
    $horario = mysqli_real_escape_string($conn, $_POST['horario']);
    $frecuencia_id = mysqli_real_escape_string($conn, $_POST['frecuencia']);
    
    $sql = "INSERT INTO medicamentos (medicamen, contenido, gramos, horario, frecuencia_id) 
            VALUES ('$medicamen', '$contenido', '$g$gramos', '$horario', '$frecuencia_id')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: medicinas.php"); 
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Lógica para obtener las opciones de frecuencia de la BD
$frecuencias = [];
$sql_frecuencia = "SELECT id_fre, nombre FROM frecuencia ORDER BY id_fre";
$result_frecuencia = $conn->query($sql_frecuencia);
if ($result_frecuencia->num_rows > 0) {
    while($row = $result_frecuencia->fetch_assoc()) {
        $frecuencias[] = $row;
    }
}

// Lógica para obtener los recordatorios y sus horarios
$horarios_alarma = [];
$sql_recordatorios = "SELECT horario FROM medicamentos";
$result_recordatorios = $conn->query($sql_recordatorios);
if ($result_recordatorios && $result_recordatorios->num_rows > 0) {
    while ($row = $result_recordatorios->fetch_assoc()) {
        $horarios_alarma[] = htmlspecialchars($row['horario']);
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
        <img src="logoalertorigi.jpeg" alt="Logo de AlertWatch"  style="width: 140px; height: 140px;">
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
                // Esta consulta ya no se usa para la lógica de la alarma, pero se mantiene para mostrar las tarjetas
                $sql = "SELECT m.*, f.nombre AS frecuencia_nombre FROM medicamentos m JOIN frecuencia f ON m.frecuencia_id = f.id_fre ORDER BY m.id_med DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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
// Lógica para el carrusel
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
carouselContainer.addEventListener('touchend', () => {
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

// Agrega esta función a tu script
function solicitarPermisoNotificaciones() {
    if (!('Notification' in window)) {
        alert('Este navegador no soporta notificaciones de escritorio.');
        return;
    }

    // Solicita el permiso al usuario
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            console.log('Permiso de notificación concedido.');
            // Aquí puedes llamar a una función para registrar la suscripción del usuario en tu servidor
        } else {
            console.warn('Permiso de notificación denegado.');
        }
    });
}
self.addEventListener('push', function(event) {
    const data = event.data.json();
    const title = data.title;
    const options = {
        body: data.body,
        icon: 'logoalertorigi.jpeg',
        badge: 'logoalertorigi.jpeg',
        // Esto es crucial para el sonido. Tu servidor debe especificar el audio.
        // Algunos navegadores móviles pueden ignorar esto por restricciones de energía.
        // La mejor práctica es que el usuario configure el tono de notificación de la app.
        sound: 'despertador.mp3' 
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(clientList => {
            for (const client of clientList) {
                if (client.url.endsWith('medicinas.php') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow('medicinas.php');
            }
        })
    );
});
// Agrega esta lógica al inicio de tu script
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
        .then(registration => {
            console.log('Service Worker registrado con éxito:', registration);
        })
        .catch(error => {
            console.error('Fallo el registro del Service Worker:', error);
        });
}

// Lógica para la alarma
const horarios = <?php echo json_encode($horarios_alarma); ?>;
const audioAlarma = new Audio('despertador.mp3'); 
audioAlarma.loop = false;

function programarProximaAlarma() {
    // Si no hay horarios, no hacemos nada
    if (horarios.length === 0) {
        return;
    }

    const ahora = new Date();
    const ahoraEnMinutos = ahora.getHours() * 60 + ahora.getMinutes();
    let proximaAlarmaEncontrada = null;
    let menorDiferencia = Infinity;
    
    // Convertir la hora actual a una cadena HH:MM para la comparación inicial
    const tiempoActual = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');

    horarios.forEach(horarioStr => {
        const [hora, minuto] = horarioStr.split(':').map(Number);
        const horarioEnMinutos = hora * 60 + minuto;
        
        // Calcular la diferencia en minutos.
        // Manejamos el caso de que el horario sea el día siguiente.
        let diferencia = horarioEnMinutos - ahoraEnMinutos;

        if (diferencia < 0) {
            // La alarma es para el día siguiente. Sumamos los minutos de un día completo.
            diferencia += 24 * 60; 
        }

        if (diferencia >= 0 && diferencia < menorDiferencia) {
            menorDiferencia = diferencia;
            proximaAlarmaEncontrada = horarioStr;
        }
    });

    if (proximaAlarmaEncontrada) {
        // Encontramos el horario más cercano.
        const [hora, minuto] = proximaAlarmaEncontrada.split(':').map(Number);
        
        const proximaAlarma = new Date();
        proximaAlarma.setHours(hora);
        proximaAlarma.setMinutes(minuto);
        proximaAlarma.setSeconds(0);
        proximaAlarma.setMilliseconds(0);

        // Si la hora ya pasó hoy, programamos para mañana
        if (proximaAlarma.getTime() < ahora.getTime()) {
             proximaAlarma.setDate(proximaAlarma.getDate() + 1);
        }
        
        const tiempoHastaAlarma = proximaAlarma.getTime() - ahora.getTime();

        // Programar la alarma para que suene en el momento exacto
        setTimeout(() => {
            audioAlarma.play();
            alert(`⏰ ¡Hora de tomar tu medicina! Son las ${proximaAlarmaEncontrada}.`);
            
            // Opcional: Eliminar la alarma ya que ya sonó una vez hoy
            const index = horarios.indexOf(proximaAlarmaEncontrada);
            if (index > -1) {
                horarios.splice(index, 1);
            }
            // Después de que suene, programa la siguiente
            programarProximaAlarma();
            
        }, tiempoHastaAlarma);
    }
}

// Llama a la función la primera vez para iniciar el proceso
programarProximaAlarma();

// Obtener elementos para el modal
var modal = document.getElementById("myModal");
var btn = document.getElementById("openModal");
var span = document.getElementsByClassName("close")[0];

// Abre el modal
btn.onclick = function() {
  modal.style.display = "block";
}

// Cierra modal
span.onclick = function() {
  modal.style.display = "none";
}

// Cierra si se hace clic fuera del modal
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
</body>
</html>