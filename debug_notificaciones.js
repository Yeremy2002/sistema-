// Script de diagnóstico específico para NOTIFICACIONES
// Para usar en la consola del navegador

console.log('🔔 DIAGNÓSTICO DE NOTIFICACIONES');
console.log('='.repeat(40));

// 1. Verificar elementos de notificaciones
console.log('\n📋 1. VERIFICANDO ELEMENTOS DE NOTIFICACIONES:');

const notificationDropdown = document.querySelector('[data-toggle="dropdown"]');
const notificationItems = document.querySelectorAll('.notification-item');
const notificationBadge = document.querySelector('.navbar-badge');
const dropdownMenu = document.querySelector('.dropdown-menu');

console.log('Dropdown de notificaciones:', notificationDropdown ? '✅ Encontrado' : '❌ No encontrado');
console.log('Items de notificación:', notificationItems.length, 'encontrados');
console.log('Badge de contador:', notificationBadge ? `✅ Encontrado (${notificationBadge.textContent})` : '❌ No encontrado');
console.log('Menu dropdown:', dropdownMenu ? '✅ Encontrado' : '❌ No encontrado');

// 2. Verificar event listeners de notificaciones
console.log('\n📋 2. VERIFICANDO EVENT LISTENERS:');

// Test del event delegation
if (notificationItems.length > 0) {
    const firstNotification = notificationItems[0];
    console.log('Primera notificación ID:', firstNotification.dataset.notificationId);
    console.log('Primera notificación URL:', firstNotification.dataset.url);
    
    // Crear evento de prueba
    const clickEvent = new Event('click', { bubbles: true, cancelable: true });
    
    console.log('🧪 Simulando clic en primera notificación...');
    
    // Capturar el evento para ver si es manejado
    let eventWasCaptured = false;
    
    document.addEventListener('click', function testHandler(e) {
        if (e.target.closest('.notification-item')) {
            eventWasCaptured = true;
            console.log('✅ Event listener de notificaciones capturó el evento');
        }
        document.removeEventListener('click', testHandler);
    });
    
    setTimeout(() => {
        firstNotification.dispatchEvent(clickEvent);
        
        setTimeout(() => {
            if (!eventWasCaptured) {
                console.log('❌ El event listener NO capturó el evento');
                console.log('   → Verificar que el script de notificaciones se está cargando');
                console.log('   → Revisar la corrección de event delegation');
            }
        }, 100);
    }, 100);
    
} else {
    console.log('ℹ️ No hay notificaciones para probar');
}

// 3. Verificar funciones de notificaciones
console.log('\n📋 3. FUNCIONES DE TEST DISPONIBLES:');

// Función para marcar todas las notificaciones como leídas
window.testMarkAllNotificationsRead = function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.log('❌ Token CSRF no disponible');
        return;
    }
    
    notificationItems.forEach((item, index) => {
        const notificationId = item.dataset.notificationId;
        if (notificationId) {
            setTimeout(() => {
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                }).then(response => {
                    console.log(`Notificación ${notificationId}:`, response.ok ? '✅ Marcada como leída' : '❌ Error');
                }).catch(error => {
                    console.log(`Notificación ${notificationId}: ❌ Error -`, error);
                });
            }, index * 200); // Espaciar las peticiones
        }
    });
};

// Función para simular clic en todas las notificaciones
window.testClickAllNotifications = function() {
    console.log('🧪 Simulando clic en todas las notificaciones...');
    
    notificationItems.forEach((item, index) => {
        setTimeout(() => {
            console.log(`Haciendo clic en notificación ${index + 1}/${notificationItems.length}`);
            item.click();
        }, index * 1000); // 1 segundo entre cada clic
    });
};

// Función para verificar rutas de notificaciones
window.testNotificationRoutes = function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.log('❌ Token CSRF no disponible');
        return;
    }
    
    console.log('🧪 Verificando rutas de notificaciones...');
    
    // Test de ruta general
    fetch('/notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    }).then(response => {
        console.log('Ruta /notifications:', response.ok ? '✅ OK' : `❌ Error ${response.status}`);
    }).catch(error => {
        console.log('Ruta /notifications: ❌ Error de red -', error);
    });
};

// 4. Información de debugging
console.log('\n📋 4. INFORMACIÓN DE DEBUG:');
console.log('UserAgent:', navigator.userAgent);
console.log('URL actual:', window.location.href);
console.log('Referrer:', document.referrer);

// 5. Verificar estilos y visibilidad
console.log('\n📋 5. VERIFICANDO ESTILOS Y VISIBILIDAD:');
if (dropdownMenu) {
    const styles = getComputedStyle(dropdownMenu);
    console.log('Display del dropdown:', styles.display);
    console.log('Visibility del dropdown:', styles.visibility);
    console.log('Opacity del dropdown:', styles.opacity);
}

if (notificationBadge) {
    const badgeStyles = getComputedStyle(notificationBadge);
    console.log('Display del badge:', badgeStyles.display);
    console.log('Color del badge:', badgeStyles.backgroundColor);
}

// 6. Instrucciones de uso
console.log('\n📋 6. FUNCIONES DE TEST DISPONIBLES:');
console.log('• testMarkAllNotificationsRead() - Marca todas las notificaciones como leídas');
console.log('• testClickAllNotifications() - Simula clic en todas las notificaciones');
console.log('• testNotificationRoutes() - Verifica que las rutas del servidor funcionen');

// 7. Monitoreo en tiempo real
console.log('\n📋 7. MONITOREO EN TIEMPO REAL:');
console.log('Se ha activado el monitoreo de eventos de notificaciones...');

// Monitorear todos los eventos de clic en el documento
document.addEventListener('click', function(e) {
    const notificationItem = e.target.closest('.notification-item');
    if (notificationItem) {
        console.log('🔔 Clic en notificación detectado:');
        console.log('  - ID:', notificationItem.dataset.notificationId);
        console.log('  - URL:', notificationItem.dataset.url);
        console.log('  - Elemento:', notificationItem);
        console.log('  - Evento preventDefault:', e.defaultPrevented);
    }
});

// Monitorear cambios en el DOM (nuevas notificaciones)
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        mutation.addedNodes.forEach(function(node) {
            if (node.nodeType === Node.ELEMENT_NODE) {
                const newNotifications = node.querySelectorAll ? node.querySelectorAll('.notification-item') : [];
                if (newNotifications.length > 0) {
                    console.log('🔔 Nuevas notificaciones detectadas en DOM:', newNotifications.length);
                }
            }
        });
    });
});

if (dropdownMenu) {
    observer.observe(dropdownMenu, { childList: true, subtree: true });
}

console.log('\n🎯 DIAGNÓSTICO DE NOTIFICACIONES COMPLETO');
console.log('='.repeat(40));
