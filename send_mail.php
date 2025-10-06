<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ELIMINADAS: Las variables locales codificadas se reciben ahora por argumento
// $motivo='Alerta';
// $bpm='78'; 
// $destinatario_email='galvantiziano21@gmail.com';
// $usuario="galvan"; 

// La ruta puede variar. Asegúrate de que esta ruta a PHPMailer sea correcta.
require_once 'vendor/autoload.php'; 

// Carga las constantes de configuración
if (!defined('EMAIL_HOST')) {
    $config_path = './email_config.php'; 
    if (file_exists($config_path)) {
        require_once $config_path;
    } else {
        error_log("Error: El archivo de configuración de email ('email_config.php') no se encuentra.");
    }
}

/**
 * Función para enviar correo de alerta manual o por umbral.
 * * Se ha modificado para aceptar 4 parámetros: email, motivo, bpm y el usuario.
 */
function enviarAlertaPorCorreo($destinatario_email, $motivo, $bpm, $usuario) { // 🚩 CAMBIO APLICADO: Añadido $usuario
    if (!defined('EMAIL_HOST') || !defined('EMAIL_USERNAME') || !defined('EMAIL_PASSWORD')) {
        echo "Error: Configuración de email incompleta.";
        return false;
    }

    $mail = new PHPMailer(true);

    // ACTIVO EL MODO DEBUG (2) para obtener el mensaje de error de Gmail si falla.
    $mail->SMTPDebug = 2; 

    try {

        // Configuración SMTP USANDO CONSTANTES
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME; // Usa 'alertwatch2000@gmail.com'
        $mail->Password   = 'nuuaeyaffxyjtdps'; // Usa la CLAVE DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = EMAIL_PORT; 
        $mail->CharSet    = 'UTF-8';
        
        // Remitente USANDO CONSTANTES
        $mail->setFrom(EMAIL_FROM, NOMBRE_EMPRESA . ' - Alerta');

        // Destinatario
        $mail->addAddress($destinatario_email);

        // Contenido del Email
        $mail->isHTML(true);
        $mail->Subject = '🚨 ALERTA DE EMERGENCIA - ' . $motivo;
        
        // Las variables $motivo, $bpm, y $usuario se usan directamente del argumento
        $cuerpo_mensaje = "
            <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-top: 5px solid #d30069;'>
                    <h2 style='color: #d30069; text-align: center;'>¡ALERTA CRÍTICA DE SALUD!</h2>
                    <p style='font-size: 16px; color: #333;'>Se ha disparado una alerta en el sistema " . NOMBRE_EMPRESA . ".</p>
                    <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                        <h4 style='color: #d30069; margin-top: 0;'>Detalles de la Alerta</h4>
                        <p style='margin: 5px 0;'><strong>Motivo:</strong> {$motivo}</p>
                        <p style='margin: 5px 0;'><strong>Usuario Monitoreado:</strong> {$usuario}</p>
                        <p style='margin: 5px 0;'><strong>Valor BPM:</strong> <span style='font-size: 20px; color: #d9534f; font-weight: bold;'>{$bpm}</span></p>
                        <p style='margin: 5px 0;'><strong>Hora de la Alerta:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    <p style='font-size: 16px; color: #555; margin-top: 20px;'>Por favor, comuníquese inmediatamente con el usuario o verifique su estado.</p>
                </div>
                <div style='background-color: #ecf0f1; padding: 20px; text-align: center; color: #7f8c8d; font-size: 14px; margin-top: 20px; border-radius: 10px;'>
                    <p style='margin: 0;'>Este es un mensaje automático de alerta de " . NOMBRE_EMPRESA . ".</p>
                </div>
            </div>";

        $mail->Body = $cuerpo_mensaje;
        $mail->AltBody = "ALERTA DE EMERGENCIA: Se ha disparado una alerta.\nMotivo: {$motivo}\\nBPM: {$bpm}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // En caso de error, el debug es capturado por index.php
        echo "Mailer Error: " . $mail->ErrorInfo; 
        return false;
    }
}
?>