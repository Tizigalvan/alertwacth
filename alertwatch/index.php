<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo conecta correctamente a tu BD

// --- INICIO DE CÓDIGO AGREGADO PARA LA AUTENTICACIÓN ---
// Verifica si el usuario NO ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirige a la página de login
    header("Location: login.php");
    exit;
}
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

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $config_bpm['min_bpm'] = $row['min_bpm'];
        $config_bpm['max_bpm'] = $row['max_bpm'];
    }

    // Generar un número aleatorio DENTRO de los umbrales cargados
    $bpm = rand($config_bpm['min_bpm'], $config_bpm['max_bpm']);
    
    $hora = date("H:i:s");
    $_SESSION['historial'][] = ['bpm' => $bpm, 'hora' => $hora];
    $_SESSION['ultimo_bpm'] = $bpm;

    echo json_encode(['bpm' => $bpm]);
    exit;
}

// Determinar qué página mostrar (monitor o configuración)
$page = $_GET['page'] ?? 'monitor';

// Cargar la configuración guardada desde la base de datos para la vista y la lógica JS
$config = ['contacto' => '', 'min_bpm' => 50, 'max_bpm' => 100];
$sql_select = "SELECT * FROM configuracion WHERE id = 1";
$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $config = $row;
}
$ultimo_bpm = $_SESSION['ultimo_bpm'] ?? 72;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AlertWatch</title>
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

.app-container {
    max-width: 400px;
    margin: auto;
    padding: 20px;
}
nav button {
    /* ... otros estilos ... */
    border-radius: 8px;
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
    background-color: #ffffff;
    }

    .app-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 40px;
     background-color: #ffffff;
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
        <a href="?page=monitor"><button class="<?= $page === 'monitor' ? 'active' : '' ?>">Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
         <a href="historial_dias.php"><button>Historial diario</button></a>
        <a href="?page=config"><button>Config</button></a>
    </nav>

    <main>
        <?php if ($page === 'monitor') : ?>
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
                <button class="whatsapp-btn" onclick="enviarAlertaWhatsApp()">Enviar Alerta</button>
            </div>
            
          
            <div class="notification-container" id="notification-container"></div>

            <script>
            let intervalo = null;
            let minBPMGuardado = <?= htmlspecialchars($config['min_bpm']) ?>;
            let maxBPMGuardado = <?= htmlspecialchars($config['max_bpm']) ?>;
            let minBPM = null;
            let maxBPM = null;
            let totalBPM = 0;
            let countBPM = 0;
            let conectado = false;

            function mostrarNotificacion(mensaje, tipo) {
                const container = document.getElementById('notification-container');
                container.textContent = mensaje;
                container.className = `notification-container ${tipo} show`;
                setTimeout(() => {
                    container.classList.remove('show');
                }, 3000);
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

                    // Lógica de validación con notificaciones
                    if (bpm < 70) {
                        mostrarNotificacion("⚠️ Tus pulsaciones están muy bajas", "high-bpm");
                    } else if (bpm > 90) {
                        mostrarNotificacion("⚠️ Tus pulsaciones están muy altas", "high-bpm");
                    } else {
                        // Opcional: Si quieres una notificación cuando están en el rango normal, descomenta la siguiente línea:
                        // mostrarNotificacion("Pulsaciones estables", "normal-bpm");
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
                }
            }

            function desconectar() {
                if (intervalo) {
                    clearInterval(intervalo);
                    intervalo = null;
                    document.getElementById('toggle-btn').textContent = 'Conectar';
                    document.getElementById('status').innerHTML = '<span class="dot red" id="status-dot"></span> Desconectado';
                    conectado = false;
                }
            }

            document.getElementById('toggle-btn').addEventListener('click', () => {
                if (conectado) {
                    desconectar();
                } else {
                    conectar();
                }
            });
            function enviarAlertaWhatsApp() {
    // El número de contacto se carga desde la base de datos
    const numeroContacto = "<?= htmlspecialchars($config['contacto']) ?>";
    const mensaje = "¡ALERTA! Las pulsaciones están fuera del rango normal. Revise el monitor de AlertWatch.";

    // Verifica que el número de contacto esté configurado
    if (numeroContacto) {
        // Codifica el mensaje para que funcione en la URL
        const mensajeCodificado = encodeURIComponent(mensaje);

        // Crea el enlace de WhatsApp
        const url = `https://wa.me/${11330188812}?text=${mensajeCodificado}`;

        // Abre el enlace en una nueva pestaña
        window.open(url, '_blank');
    } else {
        alert("Por favor, configure un número de contacto de emergencia en la sección de 'Config'.");
    }
}
            </script>
            
        <?php elseif ($page === 'config') : ?>
            <h3>Configuración</h3>
            <div class="card">
                <form method="POST">
                    <input type="hidden" name="form_action" value="save_config">
                    <label for="contacto">Contacto de Emergencia</label>
                    <input type="tel" name="contacto" id="contacto" placeholder="+1234567890" value="<?= htmlspecialchars($config['contacto']) ?>" required>
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
                <div class="info">
                    <p><span>Versión:</span> 1.5.2</p>
                    <p><span>Dispositivo:</span> No Conectado</p>
                    <p><span>Estado:</span> <span>Inactivo</span></p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>