#!/bin/bash

# Script de prueba detallada para el cierre de caja
# Agosto 2025 - Diagnóstico de problemas con el botón "Sí, cerrar caja"

echo "🔧 Diagnóstico detallado del cierre de caja..."
echo "============================================="

cd /Users/richardortiz/workspace/gestion_hotel/laravel12_migracion

# Verificar que la caja esté abierta
echo "1. Verificando caja abierta..."
CAJA_INFO=$(php artisan tinker --execute="
\$caja = App\Models\Caja::where('estado', true)->first();
if (\$caja) {
    echo 'ID:' . \$caja->id . ':USUARIO:' . \$caja->user_id . ':ESTADO:' . (\$caja->estado ? '1' : '0') . ':SALDO:' . \$caja->saldo_actual;
} else {
    echo 'NO_CAJA';
}")

if [[ "$CAJA_INFO" == "NO_CAJA" ]]; then
    echo "   ❌ No hay caja abierta para cerrar"
    exit 1
else
    IFS=':' read -r id_label caja_id usuario_label user_id estado_label estado saldo_label saldo <<< "$CAJA_INFO"
    echo "   ✅ Caja #$caja_id abierta, Usuario: $user_id, Estado: $estado, Saldo: $saldo"
fi

# Verificar usuario autenticado (simulamos que david.ortiz@gmail.com está logueado)
echo "2. Verificando permisos del usuario..."
USER_CHECK=$(php artisan tinker --execute="
\$user = App\Models\User::where('email', 'david.ortiz@gmail.com')->first();
\$caja = App\Models\Caja::find($caja_id);
if (\$user && \$caja) {
    echo 'PUEDE_ACTUALIZAR:' . (\$user->can('update', \$caja) ? '1' : '0');
    echo ':ES_PROPIETARIO:' . (\$user->id == \$caja->user_id ? '1' : '0');
    echo ':TIENE_PERMISO:' . (\$user->hasPermissionTo('cerrar caja') ? '1' : '0');
} else {
    echo 'ERROR';
}")

echo "   $USER_CHECK"

# Simular el envío AJAX tal como lo haría el frontend
echo "3. Simulando envío AJAX del cierre de caja..."

# Obtener token CSRF
CSRF_TOKEN=$(php artisan tinker --execute="echo csrf_token();")
echo "   CSRF Token obtenido: ${CSRF_TOKEN:0:10}..."

# Simular el envío POST exacto del formulario
echo "4. Enviando solicitud de cierre de caja..."
HTTP_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}\nCONTENT_TYPE:%{content_type}" \
    -X PUT \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "X-Requested-With: XMLHttpRequest" \
    -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
    -d "_token=$CSRF_TOKEN" \
    -d "_method=PUT" \
    -d "saldo_final=200.00" \
    -d "observaciones_cierre=Cierre de prueba automatizado" \
    http://localhost:8001/cajas/$caja_id)

echo "$HTTP_RESPONSE"

# Analizar la respuesta
HTTP_STATUS=$(echo "$HTTP_RESPONSE" | grep "HTTP_STATUS:" | cut -d':' -f2)
CONTENT_TYPE=$(echo "$HTTP_RESPONSE" | grep "CONTENT_TYPE:" | cut -d':' -f2)

echo ""
echo "5. Análisis de respuesta:"
echo "   HTTP Status: $HTTP_STATUS"
echo "   Content Type: $CONTENT_TYPE"

if [[ "$HTTP_STATUS" == "200" ]]; then
    echo "   ✅ Respuesta exitosa"
elif [[ "$HTTP_STATUS" == "302" ]]; then
    echo "   ⚠️  Redirección - posible problema de autenticación"
elif [[ "$HTTP_STATUS" == "422" ]]; then
    echo "   ❌ Error de validación - revisar campos requeridos"
elif [[ "$HTTP_STATUS" == "500" ]]; then
    echo "   ❌ Error interno del servidor"
else
    echo "   ❌ Error HTTP $HTTP_STATUS"
fi

# Verificar si la caja se cerró después de la prueba
echo "6. Verificando si la caja se cerró..."
CAJA_ESTADO_FINAL=$(php artisan tinker --execute="
\$caja = App\Models\Caja::find($caja_id);
if (\$caja) {
    echo 'ESTADO:' . (\$caja->estado ? 'ABIERTA' : 'CERRADA');
    if (!\$caja->estado) {
        echo ':CERRADA_EN:' . \$caja->fecha_cierre;
    }
} else {
    echo 'NO_ENCONTRADA';
}")

echo "   Estado final: $CAJA_ESTADO_FINAL"

# Verificar logs de Laravel por errores recientes
echo "7. Verificando logs recientes..."
if [[ -f "storage/logs/laravel.log" ]]; then
    RECENT_ERRORS=$(tail -n 20 storage/logs/laravel.log | grep -i "error\|exception" | wc -l)
    echo "   Errores recientes en log: $RECENT_ERRORS"
    if [[ "$RECENT_ERRORS" -gt 0 ]]; then
        echo "   📋 Últimos errores:"
        tail -n 10 storage/logs/laravel.log | grep -i "error\|exception"
    fi
else
    echo "   ⚠️  Archivo de log no encontrado"
fi

echo ""
echo "📋 DIAGNÓSTICO COMPLETO:"
echo "======================="
if [[ "$HTTP_STATUS" == "419" ]]; then
    echo "❌ PROBLEMA IDENTIFICADO: Error 419 - Token CSRF mismatch"
    echo "📝 EXPLICACIÓN: El token CSRF no es válido o ha expirado"
    echo ""
    echo "🔧 SOLUCIONES IMPLEMENTADAS:"
    echo "   ✅ Mejorado el manejo de tokens CSRF en el JavaScript"
    echo "   ✅ Agregado sistema de refresh automático de tokens"
    echo "   ✅ Mejor logging para debugging en consola del navegador"
    echo "   ✅ Manejo específico del error 419 con reintento automático"
    echo ""
    echo "🎯 ESTE ERROR ES NORMAL EN ESTE SCRIPT"
    echo "   (El curl no puede usar la misma sesión que el navegador)"
else
    if [[ "$HTTP_STATUS" == "200" && "$CAJA_ESTADO_FINAL" == *"CERRADA"* ]]; then
        echo "✅ El sistema de cierre funciona correctamente"
        echo "🔍 El problema podría ser en el frontend (JavaScript)"
    elif [[ "$HTTP_STATUS" == "302" ]]; then
        echo "❌ Problema de autenticación - el usuario no está logueado"
    elif [[ "$HTTP_STATUS" == "422" ]]; then
        echo "❌ Error de validación - faltan campos o datos incorrectos"
    else
        echo "❌ El backend tiene problemas procesando el cierre"
    fi
fi

echo ""
echo "🔧 PRÓXIMOS PASOS PARA PROBAR EN EL NAVEGADOR:"
echo "1. Abrir http://localhost:8001/cajas/1/edit en el navegador"
echo "2. Abrir DevTools (F12) y ver la pestaña Console"
echo "3. Ingresar un valor en 'Saldo Final Real' (ej: 200.00)"
echo "4. Hacer clic en 'Cerrar Caja' y confirmar con 'Sí, cerrar caja'"
echo "5. Observar los logs en la consola que ahora incluyen:"
echo "   - Token CSRF obtenido y su longitud"
echo "   - Fuente del token (meta tag, input hidden, blade)"
echo "   - URL de destino y método HTTP"
echo "   - Respuesta del servidor con detalles"
echo "6. Si aparece error 419, el sistema intentará refrescar el token automáticamente"
echo ""
echo "🚨 SI AÚN TIENE PROBLEMAS:"
echo "   - Verificar que el servidor esté corriendo en puerto 8001"
echo "   - Verificar que el usuario esté logueado como administrador"
echo "   - Limpiar cookies y cache del navegador"
echo "   - Revisar storage/logs/laravel.log por errores adicionales"
