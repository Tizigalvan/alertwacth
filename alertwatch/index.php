<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo conecta correctamente a tu BD

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
    <link rel="stylesheet" href="style.css">
    <style>
        .connect-btn {
            background-color: #d60087;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .connect-btn:hover {
            background-color: #b80071;
        }

        /* Estilos de config.php */
        .app-container{
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
            color: #333;
        }
        /* Estilos para notificaciones */
        .notification-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        .notification-container.show {
            opacity: 1;
            visibility: visible;
        }

        .low-bpm {
            background-color: #28a745; /* Verde para BPM bajo */
        }

        .high-bpm {
            background-color: #dc3545; /* Rojo para BPM alto */
        }

        .normal-bpm {
            background-color: #007bff; /* Azul para BPM normal */
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
        <a href="?page=monitor"><button class="<?= $page === 'monitor' ? 'active' : '' ?>">Monitor</button></a>
        <a href="medicinas.php"><button>Medicinas</button></a>
        <a href="historial.php"><button>Historial</button></a>
        <a href="?page=config"><button class="<?= $page === 'config' ? 'active' : '' ?>">Config</button></a>
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