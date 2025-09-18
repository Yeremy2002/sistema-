// Script de diagnóstico para el cierre de caja
// Para usar en la consola del navegador

console.log('🔧 Iniciando diagnóstico del cierre de caja...');

// 1. Verificar que jQuery está disponible
if (typeof $ !== 'undefined') {
    console.log('✅ jQuery está disponible:', $.fn.jquery);
} else {
    console.log('❌ jQuery NO está disponible');
}

// 2. Verificar que SweetAlert2 está disponible
if (typeof Swal !== 'undefined') {
    console.log('✅ SweetAlert2 está disponible');
} else {
    console.log('❌ SweetAlert2 NO está disponible');
}

// 3. Verificar si el formulario existe
const formulario = document.getElementById('formCierreCaja');
if (formulario) {
    console.log('✅ Formulario encontrado:', formulario);
} else {
    console.log('❌ Formulario NO encontrado');
}

// 4. Verificar token CSRF
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    console.log('✅ Meta CSRF encontrado:', csrfMeta.content.substring(0, 10) + '...');
} else {
    console.log('❌ Meta CSRF NO encontrado');
}

const csrfInput = document.querySelector('input[name="_token"]');
if (csrfInput) {
    console.log('✅ Input CSRF encontrado:', csrfInput.value.substring(0, 10) + '...');
} else {
    console.log('❌ Input CSRF NO encontrado');
}

// 5. Verificar campos del formulario
const saldoFinal = document.getElementById('saldo_final');
if (saldoFinal) {
    console.log('✅ Campo saldo_final encontrado, valor:', saldoFinal.value);
} else {
    console.log('❌ Campo saldo_final NO encontrado');
}

// 6. Test manual del SweetAlert
console.log('🧪 Ejecutando test de SweetAlert...');
try {
    Swal.fire({
        title: 'Test de SweetAlert',
        text: 'Si ves este mensaje, SweetAlert funciona correctamente',
        icon: 'info',
        confirmButtonText: 'OK'
    }).then((result) => {
        console.log('✅ Test SweetAlert completado:', result);
    });
} catch (error) {
    console.log('❌ Error en test SweetAlert:', error);
}

// 7. Verificar event listeners en el formulario
if (formulario) {
    console.log('🔍 Verificando event listeners del formulario...');
    
    // Simular click en el botón de submit
    const submitBtn = formulario.querySelector('button[type="submit"]');
    if (submitBtn) {
        console.log('✅ Botón submit encontrado:', submitBtn);
        
        // Test de event listener
        console.log('🧪 Disparando evento submit de prueba...');
        const testEvent = new Event('submit', { bubbles: true, cancelable: true });
        const wasDefaultPrevented = !formulario.dispatchEvent(testEvent);
        console.log('Event listener activo:', wasDefaultPrevented ? 'Sí (preventDefault llamado)' : 'No detectado');
    } else {
        console.log('❌ Botón submit NO encontrado');
    }
}

// 8. Verificar si hay errores JavaScript en la consola
console.log('🔍 Para completar el diagnóstico:');
console.log('1. Revisa si hay errores en rojo en esta consola');
console.log('2. Ve a la pestaña Network y revisa las requests al hacer clic en "Cerrar Caja"');
console.log('3. Asegúrate de tener un valor en el campo "Saldo Final Real"');
console.log('4. Intenta el proceso de cierre de caja después de este diagnóstico');

console.log('🎯 Diagnóstico completo. Revisa los resultados arriba.');
