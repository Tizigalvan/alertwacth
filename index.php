<?php
session_start();
include 'conexion.php';

// 🚩 CORRECCIÓN DEL WARNING: Definir $page al inicio 🚩
$page = $_GET['page'] ?? 'monitor'; 
// ----------------------------------------------------

// Verifica si el usuario NO ha iniciado sesión
/** @var array<string, mixed> $_SESSION */ // Pista para el Linter del editor
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirige a la página de login
    header("Location: login.php");
    exit; // Crucial para detener la ejecución
}

// 🚩 FIX APLICADO: Inicialización de variables para evitar el Warning 🚩
$mail_status = '';
$destinatario_mostrado = '';
// -----------------------------------------------------------------

$fecha_actual = date("Y-m-d");

// Cargar la última fecha guardada en la sesión
$ultima_fecha = $_SESSION['ultima_fecha'] ?? $fecha_actual;

// Si la fecha actual es diferente a la última fecha guardada, es un nuevo día
if ($fecha_actual !== $ultima_fecha) {
    // Si hay datos del día anterior en la sesión
    if (!empty($_SESSION['historial'])) {
        // Calcular estadísticas del día anterior
        $valores = array_column($_SESSION['historial'], 'bpm');
        $min_bpm_dia = count($valores) ? min($valores) : 0;
        $max_bpm_dia = count($valores) ? max($valores) : 0;
        $promedio_bpm_dia = count($valores) ? round(array_sum($valores) / count($valores)) : 0;
        $fecha_anterior = $ultima_fecha;

        // Insertar los datos en la tabla historial_diario
        $stmt = $conn->prepare("INSERT INTO historial_diario (fecha, min_bpm, max_bpm, promedio_bpm) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siii", $fecha_anterior, $min_bpm_dia, $max_bpm_dia, $promedio_bpm_dia);
        $stmt->execute();
        $stmt->close();
    }
    
    // Reiniciar el historial para el nuevo día
    $_SESSION['historial'] = [];
    $_SESSION['ultima_fecha'] = $fecha_actual;
}
// --- FIN DE CÓDIGO AGREGADO PARA LA AUTENTICACIÓN ---

// Lógica para guardar la configuración (si el formulario se envía)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'save_config') {
    $contacto = mysqli_real_escape_string($conn, $_POST['contacto'] ?? '');
    $min_bpm = mysqli_real_escape_string($conn, $_POST['min_bpm'] ?? 50);
    $max_bpm = mysqli_real_escape_string($conn, $_POST['max_bpm'] ?? 100);

    // Guardar o actualizar la configuración en la base de datos
    $sql = "INSERT INTO configuracion (id, min_bpm, max_bpm, contacto) 
             VALUES (1, '$min_bpm', '$max_bpm', '$contacto') 
             ON DUPLICATE KEY UPDATE 
             min_bpm = VALUES(min_bpm), 
             max_bpm = VALUES(max_bpm), 
             contacto = VALUES(contacto)";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?page=config&status=success");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Lógica para el monitor de BPM (si es una solicitud AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_bpm') {
    // Cargar los umbrales de la base de datos para generar el número
    $sql_select = "SELECT * FROM configuracion WHERE id = 1";
    $result = $conn->query($sql_select);
    $config_bpm = ['min_bpm' => 50, 'max_bpm' => 100]; // Valores por defecto
    $config = ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100]; 

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $config_bpm['min_bpm'] = $row['min_bpm'];
        $config_bpm['max_bpm'] = $row['max_bpm'];
        $config['contacto'] = $row['contacto']; // <-- Carga el contacto
    }

    // Generar un número aleatorio DENTRO de los umbrales cargados
    $bpm = rand($config_bpm['min_bpm'], $config_bpm['max_bpm']);
    
    $hora = date("H:i:s");
    $_SESSION['historial'][] = ['bpm' => $bpm, 'hora' => $hora];
    $_SESSION['ultimo_bpm'] = $bpm;

 
    echo json_encode(['bpm' => $bpm]);
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_mail_manual') {
    // =========================================================================
    // 🚩 LÓGICA: Envío de Correo Manual (send_mail_manual) con DIAGNÓSTICO 🚩
    // =========================================================================
    // 1. Cargar la configuración y el último BPM 
    $sql_select = "SELECT * FROM configuracion WHERE id = 1";
    $result = $conn->query($sql_select);
    $config = ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100]; 
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $config['contacto'] = $row['contacto']; // Se carga el contacto (teléfono o email)
    }
    
    $ultimo_bpm = $_SESSION['ultimo_bpm'] ?? 0;
    $usuario_logueado = $_SESSION['user_name'] ?? 'tiziano galvan'; 
    $email_destino = $config['contacto']; // <<-- CAMBIO REALIZADO: Usa el valor del array $config['contacto']
    $motivo = "Alerta Manual (BPM: $ultimo_bpm)";

    // 2. Usar output buffering para CAPTURAR la salida de send_mail.php
    ob_start();
    $envio_exitoso = false;
    $message = '';
    // Intentar incluir y ejecutar la función de envío de correo
    if (file_exists('send_mail.php')) {
        include 'send_mail.php';
        
        if (function_exists('enviarAlertaPorCorreo')) {
            // Llama a la función que definiremos en send_mail.php
            $envio_exitoso = enviarAlertaPorCorreo($email_destino, $motivo, $ultimo_bpm, $usuario_logueado);
            
            if ($envio_exitoso) {
                $message = "Correo de alerta enviado exitosamente.";
            } else {
                $message = "Fallo al enviar el correo. Verifique logs o salida de PHPMailer.";
            }
        } else {
            $message = "Error: La función 'enviarAlertaPorCorreo' no se encuentra en send_mail.php. Verifica su contenido.";
        }
    } else {
        $message = "Error: El archivo 'send_mail.php' no existe o no se pudo incluir.";
    }

    $debug_output = ob_get_clean(); 
    
    // 3. Devolver una respuesta JSON
    if ($envio_exitoso) {
        $response_data = [
            'status' => 'success',
            'message' => $message,
            'destino' => $email_destino
        ];
    } else {
        $response_data = [
            'status' => 'error',
            'message' => $message,
            'destino' => $email_destino,
            'debug' => $debug_output 
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response_data);
    exit; 
}
    // =========================================================================
    // 🚩 FIN LÓGICA ALERTA MANUAL 🚩
    // =========================================================================


// Cargar la configuración guardada desde la base de datos para la vista
$config = ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100];
$sql_select = "SELECT * FROM configuracion WHERE id = 1";
$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $config = $row;
}
$ultimo_bpm = $_SESSION['ultimo_bpm'] ?? 0;

// Código PHP para alarmas de medicamentos
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
    <title>AlertWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
/* Estilos se mantienen igual */
 body {
            font-family: 'Arial', sans-serif;
             background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .app-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            color: #d30069;
        }

        nav {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        nav a {
            margin: 0 10px;
        }

        nav button {
            background-color: #f4f4f4;
            color: #333;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        nav button.active {
            background-color: #d60087;
            color: white;
        }

        main {
            padding: 10px 0;
        }

        h3 {
            text-align: center;
            font-size: 20px;
            color: #d60087;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .card label {
            font-size: 14px;
            color: #555;
            display: block;
            margin-bottom: 8px;
        }

        .card input[type="tel"],
        .card input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            font-size: 14px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .card button.submit-btn {
            background-color: #d60087;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        .card button.submit-btn:hover {
            background-color: #b80071;
        }

        .card .info p {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }

        .card .info span {
            font-weight: bold;
        }

        .card .info p span {
            color: red;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .stat {
            text-align: center;
            font-size: 14px;
        }

        .stat h3 {
            font-size: 20px;
          
        }

body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background: #fdfdfd;
    color: #333;
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
nav a {
    text-decoration: none;
    margin-right: -5px; /* Margen negativo para acercar los enlaces */
}
nav {
    display: flex;
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
    color:  green;
}

.blue {
    color: ;
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

/* Contenedor de la notificación (oculto por defecto) */
.notification-container {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.4s ease; /* Transición suave para aparecer y desaparecer */
    opacity: 0;
    transform: translateY(-20px);
    visibility: hidden;
}
/* Estado cuando la notificación debe mostrarse */
.notification-container.show {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
}
/* Estilo para la notificación de BPMs altos o bajos (high-bpm) */
.notification-container.high-bpm {
    background-color: #e74c3c; /* Un rojo vivo para indicar peligro */
    border: 2px solid #c0392b; /* Borde más oscuro para contraste */
}
/* Estilo para la notificación si las pulsaciones vuelven a la normalidad */
.notification-container.normal-bpm {
    background-color: #2ecc71; /* Verde para indicar que todo está bien */
    border: 2px solid #27ae60;
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
/*
 * ============================================================================
 * Estilos para Notificaciones de Alerta
 * ============================================================================
 */

/* Contenedor de la notificación (oculto por defecto) */
.notification-container {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.4s ease; /* Transición suave para aparecer y desaparecer */
    opacity: 0;
    transform: translateY(-20px);
    visibility: hidden;
}
/* Estado cuando la notificación debe mostrarse */
.notification-container.show {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
}
/* Estilo para la notificación de BPMs altos o bajos (high-bpm) */
.notification-container.high-bpm {
    background-color: #e74c3c; /* Un rojo vivo para indicar peligro */
    border: 2px solid #c0392b; /* Borde más oscuro para contraste */
}
/* Estilo para la notificación si las pulsaciones vuelven a la normalidad */
.notification-container.normal-bpm {
    background-color: #2ecc71; /* Verde para indicar que todo está bien */
    border: 2px solid #27ae60;
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
<audio id="alert-sound" src="despertador.mp3" preload="auto"></audio> 
<div class="app-container">
    <header>
        <img src="logoalertorigi.jpeg" alt="Logo de AlertWatch" style="width: 140px; height: 140px;">
        <p>Tu salud cardíaca bajo control</p>
    </header>
    <nav>
        <a href="?page=monitor"><button class="<?= $page === 'monitor' ? 'active' : '' ?>">Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
            <a href="historial_dias.php"><button>Historial diario</button></a>
        <a href="?page=config"><button>Config</button></a>
    </nav>
    <main>
        <?php if ($page === 'monitor') : ?>    
            <?php if ($mail_status === 'mail_success') : ?>
                <p style="color: green; font-weight: bold; text-align: center; margin-bottom: 20px;">
                    ✅ Correo enviado con éxito a **<?= htmlspecialchars($destinatario_mostrado) ?>**.
                </p>
            <?php elseif ($mail_status === 'mail_error') : ?>
                <p style="color: red; font-weight: bold; text-align: center; margin-bottom: 20px;">
                    ❌ Error al enviar el correo. Revisa el log de errores del servidor y la **App Password** en `includes/mail.php`.
                </p>
            <?php endif; ?>
            <div class="notification-container" id="notification-container"></div>
            <div class="monitor-card">
                <p class="status" id="status"><span class="dot red" id="status-dot"></span> Desconectado</p>
                <h2 class="bpm" id="bpm-value"><?= $ultimo_bpm ?></h2>
                <p class="bpm-label">BPM</p>
                <button class="connect-btn" id="toggle-btn">Conectar</button>
            </div>
            <div class="stats">
                <div class="stat">
                    <p>Mínimo</p>
                    <h3 class="green" id="min-bpm">0</h3>
                </div>
                <div class="stat">
                    <p>Promedio</p>
                    <h3 class="blue" id="avg-bpm">0</h3>
                </div>
                <div class="stat">
                    <p>Máximo</p>
                    <h3 class="red" id="max-bpm">0</h3>
                </div>
               <br>
</div>   <button class="connect-btn" id="send-mail-btn" style="background: #007bff; margin-top: 20px; width: 100%;">
 <span id="mail-btn-text">Mandar Correo Ahora</span>
 <span id="mail-btn-spinner" style="display: none;">Enviando...</span>
 </button>
 <p id="mail-status-msg" style="margin-top: 10px; font-weight: bold; text-align: center;"></p>
</form>

            </div>
            <script>
            let intervalo = null;
            let minBPMGuardado = <?= htmlspecialchars($config['min_bpm']) ?>;
            let maxBPMGuardado = <?= htmlspecialchars($config['max_bpm']) ?>;
            
            // Umbrales Fijos para esta solicitud
            const UMBRAL_MINIMO_ALERTA = 70;
            const UMBRAL_MAXIMO_ALERTA = 90;

            let minBPM = null;
            let maxBPM = null;
            let totalBPM = 0;
            let countBPM = 0;
            let conectado = false;
            const alertSound = document.getElementById('alert-sound');


            function mostrarNotificacion(mensaje, tipo) {
                const container = document.getElementById('notification-container');
                container.textContent = mensaje;
                container.className = `notification-container ${tipo} show`;
                // La notificación permanece hasta que se normaliza o se desconecta
            }
            
            function ocultarNotificacion() {
                 const container = document.getElementById('notification-container');
                 container.classList.remove('show');
            }

            function reproducirAlerta() {
                if (alertSound) {
                    // Reiniciar y reproducir el sonido
                    alertSound.currentTime = 0; 
                    alertSound.play().catch(error => {
                        console.log("Error al reproducir el sonido de alerta.");
                    });
                }
            }
            
            function detenerAlerta() {
                 if (alertSound && !alertSound.paused) {
                      alertSound.pause();
                      alertSound.currentTime = 0;
                 }
            }

            function obtenerBPM() {
                fetch('index.php', {
                    method: 'POST',
                    body: new URLSearchParams({ 'action': 'get_bpm' }),
                })
                .then(response => response.json())
                .then(data => {
                    const bpm = data.bpm;
                    document.getElementById('bpm-value').textContent = bpm;
                    actualizarEstadisticas(bpm);

                    // Lógica de validación con notificaciones y sonido
                    if (bpm < UMBRAL_MINIMO_ALERTA) {
                        mostrarNotificacion(`⚠️ ¡ALARMA! Pulsaciones muy bajas: ${bpm} BPM`, "high-bpm");
                        reproducirAlerta();
                    } else if (bpm > UMBRAL_MAXIMO_ALERTA) {
                        mostrarNotificacion(`⚠️ ¡ALARMA! Pulsaciones muy altas: ${bpm} BPM`, "high-bpm");
                        reproducirAlerta();
                    } else {
                        // Ocultar notificación y detener sonido si vuelve al rango normal
                        ocultarNotificacion();
                        detenerAlerta();
                    }
                })
                .catch(error => console.error('Error al obtener el BPM:', error));
            }

            function actualizarEstadisticas(bpm) {
                if (minBPM === null || bpm < minBPM) {
                    minBPM = bpm;
                    document.getElementById('min-bpm').textContent = minBPM;
                }

                if (maxBPM === null || bpm > maxBPM) {
                    maxBPM = bpm;
                    document.getElementById('max-bpm').textContent = maxBPM;
                }

                totalBPM += bpm;
                countBPM++;
                const avgBPM = (totalBPM / countBPM).toFixed(2);
                document.getElementById('avg-bpm').textContent = avgBPM;
            }

            function conectar() {
                if (!intervalo) {
                    intervalo = setInterval(obtenerBPM, 1000);
                    document.getElementById('toggle-btn').textContent = 'Desconectar';
                    document.getElementById('status').innerHTML = '<span class="dot green" id="status-dot"></span> Conectado';
                    conectado = true;
                    // Llamada inicial para cargar el primer valor
                    obtenerBPM(); 
                }
            }

            function desconectar() {
                if (intervalo) {
                    clearInterval(intervalo);
                    intervalo = null;
                    document.getElementById('toggle-btn').textContent = 'Conectar';
                    document.getElementById('status').innerHTML = '<span class="dot red" id="status-dot"></span> Desconectado';
                    conectado = false;
                    // Detener todo al desconectar
                    ocultarNotificacion();
                    detenerAlerta();
                }
            }

            document.getElementById('toggle-btn').addEventListener('click', () => {
                if (conectado) {
                    desconectar();
                } else {
                    conectar();
                }
            });
            
          // 🚩 FUNCIÓN NUEVA: Envío de Correo Manual (a send_mail_manual en PHP) 🚩
function enviarCorreoManual() {
    const btn = document.getElementById('send-mail-btn');
    const msg = document.getElementById('mail-status-msg');
    
    btn.disabled = true;
    document.getElementById('mail-btn-text').style.display = 'none';
    document.getElementById('mail-btn-spinner').style.display = 'inline';
    msg.textContent = ''; // Limpiar mensaje anterior
    msg.style.color = 'black';
    document.getElementById('mail-btn-spinner').textContent = 'Enviando...';

    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        // Llama a la nueva acción PHP
        body: new URLSearchParams({ 'action': 'send_mail_manual' }), 
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { 
                throw new Error('Error de servidor (' + response.status + '): ' + text.substring(0, 100) + '...');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            msg.textContent = `✅ Correo enviado a ${data.destino}.`;
            msg.style.color = 'green';
        } else {
            msg.textContent = `❌ Error al enviar. Revise la Consola (F12). Mensaje: ${data.message}`;
            msg.style.color = 'red';
            if (data.debug) {
                 console.error('Debug de Correo (POSIBLE ERROR PHPMailer):', data.debug);
            }
        }
    })
    .catch(error => {
        msg.textContent = `❌ Error de comunicación: ${error.message}`;
        msg.style.color = 'red';
        console.error('Error AJAX/Fetch:', error);
    })
    .finally(() => {
        btn.disabled = false;
        document.getElementById('mail-btn-text').style.display = 'inline';
        document.getElementById('mail-btn-spinner').style.display = 'none';
    });
}

// Event listener para el nuevo botón
document.getElementById('send-mail-btn').addEventListener('click', enviarCorreoManual);

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
        <?php elseif ($page === 'config') : ?>
            <h3>Configuración</h3>
            <div class="card">
                <form method="POST">
                    <input type="hidden" name="form_action" value="save_config">
                    <label for="contacto">Contacto de Emergencia</label>
                    <input type="text" name="contacto" id="contacto" placeholder="contacto@gmail.com" value="<?= htmlspecialchars($config['contacto']) ?>" required>
                      <h4>Umbrales de Alerta</h4>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" name="min_bpm" placeholder="Mínimo BPM" value="<?= htmlspecialchars($config['min_bpm']) ?>" required>
                        <input type="number" name="max_bpm" placeholder="Máximo BPM" value="<?= htmlspecialchars($config['max_bpm']) ?>" required>
                    </div>
                    <button type="submit" class="submit-btn">Guardar Configuración</button>
                </form>
            </div>
            <div class="card">
                <h4>Información de la App</h4>
                <div>
                    <p class ="p"><span>Versión:</span  class= "c"> 1.5.2</p>
                    <p class ="p"><span>Dispositivo:</span  class= "c"> No Conectado</p>
                    <p class ="p"><span>Estado:</span> <span class= "c">Inactivo</span></p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>