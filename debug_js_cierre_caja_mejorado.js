// Script de diagnóstico MEJORADO para el cierre de caja
// Para usar en la consola del navegador en la página de editar caja

console.log('🔧 DIAGNÓSTICO AVANZADO - CIERRE DE CAJA');
console.log('='.repeat(50));

// 1. Verificar dependencias básicas
console.log('\n📋 1. VERIFICANDO DEPENDENCIAS BÁSICAS:');
let dependenciasOK = true;

if (typeof $ !== 'undefined') {
    console.log('✅ jQuery está disponible (versión:', $.fn.jquery, ')');
} else {
    console.log('❌ jQuery NO está disponible');
    dependenciasOK = false;
}

if (typeof Swal !== 'undefined') {
    console.log('✅ SweetAlert2 está disponible');
} else {
    console.log('❌ SweetAlert2 NO está disponible');
    dependenciasOK = false;
}

// 2. Verificar elementos del DOM
console.log('\n📋 2. VERIFICANDO ELEMENTOS DEL DOM:');
const formulario = document.getElementById('formCierreCaja');
const saldoFinal = document.getElementById('saldo_final');
const submitBtn = formulario?.querySelector('button[type="submit"]');
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfInput = document.querySelector('input[name="_token"]');

let elementosOK = true;

if (formulario) {
    console.log('✅ Formulario encontrado:', formulario.action);
} else {
    console.log('❌ Formulario NO encontrado');
    elementosOK = false;
}

if (saldoFinal) {
    console.log('✅ Campo saldo_final encontrado, valor actual:', saldoFinal.value || '(vacío)');
} else {
    console.log('❌ Campo saldo_final NO encontrado');
    elementosOK = false;
}

if (submitBtn) {
    console.log('✅ Botón submit encontrado:', submitBtn.textContent?.trim());
} else {
    console.log('❌ Botón submit NO encontrado');
    elementosOK = false;
}

// 3. Verificar tokens CSRF
console.log('\n📋 3. VERIFICANDO TOKENS CSRF:');
let csrfOK = false;

if (csrfMeta && csrfMeta.content) {
    console.log('✅ Meta CSRF encontrado (longitud:', csrfMeta.content.length, ')');
    csrfOK = true;
} else {
    console.log('❌ Meta CSRF NO encontrado o vacío');
}

if (csrfInput && csrfInput.value) {
    console.log('✅ Input CSRF encontrado (longitud:', csrfInput.value.length, ')');
    csrfOK = true;
} else {
    console.log('❌ Input CSRF NO encontrado o vacío');
}

// 4. Test de SweetAlert
console.log('\n📋 4. TEST DE SWEETALERT:');
if (typeof Swal !== 'undefined') {
    try {
        Swal.fire({
            title: 'Test Diagnóstico',
            text: 'Si ves este modal, SweetAlert funciona correctamente',
            icon: 'info',
            showConfirmButton: true,
            timer: 3000,
            confirmButtonText: 'Entendido'
        });
        console.log('✅ Test SweetAlert ejecutado correctamente');
    } catch (error) {
        console.log('❌ Error en test SweetAlert:', error);
    }
}

// 5. Verificar event listeners
console.log('\n📋 5. VERIFICANDO EVENT LISTENERS:');
if (formulario) {
    // Crear un evento de prueba para ver si hay listeners activos
    const testEvent = new Event('submit', { bubbles: true, cancelable: true });
    const wasDefaultPrevented = !formulario.dispatchEvent(testEvent);
    
    if (wasDefaultPrevented) {
        console.log('✅ Event listener activo (preventDefault fue llamado)');
    } else {
        console.log('❌ No se detectó event listener activo o no previene el envío');
    }
}

// 6. Verificar funciones específicas del sistema
console.log('\n📋 6. VERIFICANDO FUNCIONES DEL SISTEMA:');
if (typeof setupEventHandlers === 'function') {
    console.log('✅ Función setupEventHandlers disponible');
} else {
    console.log('❌ Función setupEventHandlers NO disponible');
}

if (typeof imprimirTicket === 'function') {
    console.log('✅ Función imprimirTicket disponible');
} else {
    console.log('❌ Función imprimirTicket NO disponible');
}

// 7. Diagnóstico de la consola
console.log('\n📋 7. DIAGNÓSTICO GENERAL:');
console.log('Estado general de dependencias:', dependenciasOK ? '✅ OK' : '❌ PROBLEMAS');
console.log('Estado general de elementos DOM:', elementosOK ? '✅ OK' : '❌ PROBLEMAS');
console.log('Estado general de CSRF:', csrfOK ? '✅ OK' : '❌ PROBLEMAS');

// 8. Recomendaciones
console.log('\n📋 8. RECOMENDACIONES Y PASOS SIGUIENTES:');

if (!dependenciasOK) {
    console.log('⚠️ CRÍTICO: Faltan dependencias JavaScript básicas');
    console.log('   → Verificar que jQuery y SweetAlert2 se carguen correctamente');
    console.log('   → Revisar errores en consola (pestaña Console)');
    console.log('   → Verificar rutas de archivos JS en el HTML');
}

if (!elementosOK) {
    console.log('⚠️ ERROR: Elementos DOM faltantes');
    console.log('   → Verificar que estás en la página correcta (/cajas/{id}/edit)');
    console.log('   → Verificar que el formulario tiene ID "formCierreCaja"');
    console.log('   → Verificar que el campo tiene ID "saldo_final"');
}

if (!csrfOK) {
    console.log('⚠️ ERROR: Token CSRF no disponible');
    console.log('   → Recargar la página');
    console.log('   → Verificar que el meta tag csrf-token está presente');
}

console.log('\n📋 9. PARA PROBAR EL CIERRE DE CAJA:');
console.log('1. Asegúrate de que todos los elementos anteriores estén ✅');
console.log('2. Ingresa un valor en el campo "Saldo Final Real"');
console.log('3. Haz clic en "Cerrar Caja"');
console.log('4. Ve a la pestaña "Network" para ver las peticiones AJAX');
console.log('5. Observa esta consola para ver los logs del proceso');

// 10. Test manual del flujo completo
console.log('\n📋 10. FUNCIÓN DE TEST MANUAL:');
console.log('Ejecuta: testCierreCaja() para simular el proceso completo');

window.testCierreCaja = function() {
    if (!saldoFinal || !formulario) {
        console.log('❌ No se puede ejecutar test: elementos faltantes');
        return;
    }
    
    // Llenar saldo si está vacío
    if (!saldoFinal.value) {
        saldoFinal.value = '100.00';
        console.log('ℹ️ Se asignó valor de prueba: 100.00');
    }
    
    console.log('🧪 Simulando clic en botón de cerrar caja...');
    
    // Simular el clic
    if (submitBtn) {
        submitBtn.click();
    } else {
        // Disparar evento submit directamente
        formulario.dispatchEvent(new Event('submit', { bubbles: true }));
    }
};

// 11. Test de conexión con el servidor
console.log('\n📋 11. TEST DE CONEXIÓN AL SERVIDOR:');
console.log('Ejecuta: testConexionServidor() para verificar la conectividad');

window.testConexionServidor = function() {
    if (!csrfMeta || !csrfMeta.content) {
        console.log('❌ No se puede ejecutar test: token CSRF no disponible');
        return;
    }
    
    console.log('🧪 Enviando petición de prueba al servidor...');
    
    // Crear una petición simple para verificar la conexión
    fetch('/ping', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfMeta.content
        }
    })
    .then(response => {
        console.log(`✅ Respuesta del servidor: ${response.status} ${response.statusText}`);
        return response.text();
    })
    .then(data => {
        console.log('Datos recibidos:', data || '(vacío)');
    })
    .catch(error => {
        console.log('❌ Error de conexión:', error);
        console.log('→ Esto puede indicar problemas de red o CORS');
    });
};

console.log('\n🎯 DIAGNÓSTICO COMPLETO');
console.log('='.repeat(50));
console.log('Si todo está ✅, el problema puede estar en:');
console.log('- La respuesta del servidor (revisa Network tab)');
console.log('- Errores JavaScript no capturados');
console.log('- Problemas de conectividad o permisos');
console.log('\nEjecuta testCierreCaja() para una prueba completa');
