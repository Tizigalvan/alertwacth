<?php
session_start();
include 'conexion.php';

// Obtener historial del día actual (desde la sesión)
$historial_sesion = $_SESSION['historial'] ?? [];

// Obtener historial de días anteriores (desde la base de datos)
$sql_historial_db = "SELECT fecha, min_bpm, max_bpm, promedio_bpm FROM historial_diario ORDER BY fecha DESC";
$result_historial_db = $conn->query($sql_historial_db);
$historial_db = $result_historial_db->fetch_all(MYSQLI_ASSOC);

// Calcular estadísticas del día actual (para las tarjetas del historial)
$valores_hoy = array_column($historial_sesion, 'bpm');
$minimo_hoy = count($valores_hoy) ? min($valores_hoy) : 0;
$maximo_hoy = count($valores_hoy) ? max($valores_hoy) : 0;
$promedio_hoy = count($valores_hoy) ? round(array_sum($valores_hoy) / count($valores_hoy)) : 0;

// ... código PHP existente (carga de configuración)

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
    <title>Historial - AlertWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <header>
        <img src="logoalertorigi.jpeg" alt="Logo de AlertWatch" style="width: 140px; height: 140px;">
        <p>Tu salud cardíaca bajo control</p>
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
    </header>

    <nav>
        <a href="index.php"><button>Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button class="active">Historial</button></a>
        <a href="historial_dias.php"><button>Historial diario</button></a>
        <a href="config.php"><button>Config</button></a>
    </nav>

    <main>
        <h3>Historial de Hoy</h3>
        <div class="stats">
            <div class="stat">
                <p>Mínimo Hoy</p>
                <h3 class="green"><?= $minimo_hoy ?></h3>
            </div>
            <div class="stat">
                <p>Promedio</p>
                <h3 class="blue"><?= $promedio_hoy ?></h3>
            </div>
            <div class="stat">
                <p>Máximo Hoy</p>
                <h3 class="red"><?= $maximo_hoy ?></h3>
            </div>
        </div>

        <div class="history-list">
            <h4>Lecturas Recientes</h4>
            <div class="scrollable">
                <?php foreach (array_reverse($historial_sesion) as $lectura): ?>
                    <p><?= $lectura['hora'] ?> <span style="color: green; float:right;"><?= $lectura['bpm'] ?> BPM</span></p>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
   <script>
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

</script>
</div>

</body>
</html>