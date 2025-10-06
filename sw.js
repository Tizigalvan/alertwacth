// sw.js

// Al instalar, el Service Worker se activa inmediatamente.
self.addEventListener('install', event => {
    console.log('Service Worker: Instalado');
    self.skipWaiting();
});

// Al activar, toma el control de las páginas para poder interactuar con ellas.
self.addEventListener('activate', event => {
    console.log('Service Worker: Activado');
    event.waitUntil(clients.claim());
});

// Escuchamos los mensajes de la página web.
// Esta es la forma en que la página le enviará los horarios al Service Worker.
self.addEventListener('message', event => {
    if (event.data && event.data.action === 'setAlarm') {
        const horarios = event.data.horarios;
        console.log('Service Worker: Alarmas recibidas:', horarios);

        // Limpiamos cualquier temporizador de alarma anterior para evitar duplicados.
        if (self.alarmInterval) {
            clearInterval(self.alarmInterval);
        }

        // Creamos un nuevo temporizador para verificar los horarios de los medicamentos.
        self.alarmInterval = setInterval(() => {
            const ahora = new Date();
            const horaActualStr = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');

            horarios.forEach(item => {
                // Comparamos el horario de la alarma con la hora actual.
                // Usamos un identificador único para evitar que la alarma suene varias veces
                // en el mismo minuto si hay varios clientes (páginas) abiertos.
                const alarmaId = `${item.horario}-${item.medicamento}`;
                
                // Si la alarma no se ha disparado en el minuto actual, la disparamos.
                if (item.horario === horaActualStr && (!self.alarmasDisparadas || !self.alarmasDisparadas[alarmaId])) {
                    console.log(`Service Worker: ¡Alarma disparada para ${item.medicamento}!`);

                    // Guardamos la alarma como "disparada" para el minuto actual.
                    if (!self.alarmasDisparadas) {
                        self.alarmasDisparadas = {};
                    }
                    self.alarmasDisparadas[alarmaId] = true;

                    // Mostramos la notificación.
                    self.registration.showNotification('🚨 ALERTA DE MEDICAMENTO', {
                        body: `Es hora de tomar: ${item.medicamento} a las ${item.horario}.`,
                        icon: 'logoalertorigi.jpeg',
                        vibrate: [200, 100, 200, 100, 200],
                        sound: 'despertador.mp3', 
                    }).catch(error => {
                        console.error('Service Worker: Error al mostrar la notificación:', error);
                    });
                }
            });
        }, 1000); // Verificamos cada segundo para mayor precisión.
    }
});

// Reiniciamos el registro de alarmas cada minuto.
self.addEventListener('message', event => {
    if (event.data && event.data.action === 'resetAlarms') {
        if (self.alarmasDisparadas) {
            self.alarmasDisparadas = {};
            console.log("Service Worker: Registro de alarmas reiniciado.");
        }
    }
});

// Manejamos el clic en la notificación para abrir la página de medicinas.
self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('medicinas.php, index.php, config.php,  historial.php,  historial_dias.php')
    );
});