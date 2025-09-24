<?php

// Reemplaza 'TU_CLAVE_DEL_SERVIDOR_FCM_AQUÍ' con tu clave real de Firebase
define('API_ACCESS_KEY', 'TU_CLAVE_DEL_SERVIDOR_FCM_AQUÍ');

// URL del servicio de Firebase
$url = 'https://fcm.googleapis.com/fcm/send';

// Contenido de la notificación
$notification = array(
    'title' => '¡Alerta de AlertWatch!',
    'body' => 'Tus pulsaciones están fuera del rango normal. ¡Revisa tu monitor!',
    'icon' => 'your_icon_name' // Nombre de un icono que tengas en tu app
);

// Datos a enviar a Firebase
$fields = array(
    // Reemplaza 'TOKEN_DEL_DISPOSITIVO_AQUÍ' con el token del celular
    'to' => 'TOKEN_DEL_DISPOSITIVO_AQUÍ', 
    'notification' => $notification
);

// Convertir los datos a formato JSON
$fields = json_encode($fields);

// Configurar los encabezados de la solicitud
$headers = array(
    'Authorization: key=' . API_ACCESS_KEY,
    'Content-Type: application/json'
);

// Usar cURL para hacer la solicitud a la API de Firebase
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

// Ejecutar la solicitud
$result = curl_exec($ch);

// Cerrar la conexión
curl_close($ch);

// Imprimir el resultado para depuración
echo $result;

?>