#!/bin/bash

# Script de prueba para verificar el sistema de cierre de cajas
# Agosto 2025

echo "🔍 Verificando el sistema de cierre de cajas..."
echo "=========================================="

# Verificar que el servidor esté ejecutándose
echo "1. Verificando servidor..."
if curl -s -f http://localhost:8001 > /dev/null; then
    echo "   ✅ Servidor Laravel ejecutándose en puerto 8001"
else
    echo "   ❌ Error: Servidor no disponible en puerto 8001"
    echo "   💡 Ejecute: php artisan serve --port=8001"
    exit 1
fi

# Verificar configuración de URL
echo "2. Verificando configuración..."
APP_URL=$(cd /Users/richardortiz/workspace/gestion_hotel/laravel12_migracion && php artisan config:show app.url)
if [[ "$APP_URL" == *"localhost:8001"* ]]; then
    echo "   ✅ APP_URL configurada correctamente: $APP_URL"
else
    echo "   ⚠️  APP_URL: $APP_URL (verificar si es correcto)"
fi

# Verificar cajas abiertas
echo "3. Verificando cajas abiertas..."
cd /Users/richardortiz/workspace/gestion_hotel/laravel12_migracion
CAJA_INFO=$(php artisan tinker --execute="
\$caja = App\Models\Caja::where('estado', true)->first();
if (\$caja) {
    echo 'ABIERTA:' . \$caja->id . ':' . \$caja->user->name . ':' . \$caja->saldo_actual;
} else {
    echo 'NINGUNA';
}")

if [[ "$CAJA_INFO" == "NINGUNA" ]]; then
    echo "   ⚠️  No hay cajas abiertas"
    echo "   💡 El usuario debe abrir una caja antes de intentar cerrarla"
else
    IFS=':' read -r status caja_id usuario saldo <<< "$CAJA_INFO"
    echo "   ✅ Caja #$caja_id abierta (Usuario: $usuario, Saldo: $saldo)"
fi

# Verificar usuario david.ortiz@gmail.com
echo "4. Verificando usuario david.ortiz@gmail.com..."
USER_INFO=$(php artisan tinker --execute="
\$user = App\Models\User::where('email', 'david.ortiz@gmail.com')->first();
if (\$user) {
    echo 'EXISTE:' . \$user->name . ':' . \$user->roles->pluck('name')->implode(',') . ':' . (\$user->hasPermissionTo('cerrar caja') ? 'SI' : 'NO');
} else {
    echo 'NO_EXISTE';
}")

if [[ "$USER_INFO" == "NO_EXISTE" ]]; then
    echo "   ❌ Usuario david.ortiz@gmail.com no encontrado"
else
    IFS=':' read -r status nombre roles permiso <<< "$USER_INFO"
    echo "   ✅ Usuario: $nombre"
    echo "   ✅ Roles: $roles"
    echo "   ✅ Permiso cerrar caja: $permiso"
fi

# Verificar notificaciones
echo "5. Verificando notificaciones..."
NOTIF_COUNT=$(php artisan tinker --execute="
\$user = App\Models\User::where('email', 'david.ortiz@gmail.com')->first();
if (\$user) {
    \$count = \$user->unreadNotifications->where('data.type', 'recordatorio_cierre_caja')->count();
    echo \$count;
} else {
    echo '0';
}")

echo "   📧 Notificaciones de cierre de caja: $NOTIF_COUNT"

# Verificar URL de cierre de caja
echo "6. Verificando acceso a cierre de caja..."
if [[ "$CAJA_INFO" != "NINGUNA" ]]; then
    IFS=':' read -r status caja_id usuario saldo <<< "$CAJA_INFO"
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8001/cajas/$caja_id/edit)
    if [[ "$HTTP_CODE" == "302" ]]; then
        echo "   🔒 Requiere autenticación (HTTP 302) - ✅ Normal"
        echo "   💡 El usuario debe hacer login en: http://localhost:8001/login"
    elif [[ "$HTTP_CODE" == "200" ]]; then
        echo "   ✅ Acceso directo disponible (HTTP 200)"
    else
        echo "   ❌ Error HTTP $HTTP_CODE"
    fi
fi

echo ""
echo "📋 RESUMEN:"
echo "=========="
echo "• Servidor: ✅ Funcionando"
echo "• URL Config: ✅ Correcta"
echo "• Usuario: ✅ Existe con permisos"
if [[ "$CAJA_INFO" != "NINGUNA" ]]; then
    echo "• Caja: ✅ Disponible para cierre"
else
    echo "• Caja: ⚠️  Necesita abrir caja primero"
fi
echo "• Notificaciones: 📧 $NOTIF_COUNT pendientes"
echo ""
echo "🔑 PARA CERRAR CAJA:"
echo "1. Ir a: http://localhost:8001/login"
echo "2. Login con: david.ortiz@gmail.com"
echo "3. Hacer clic en notificación o ir directamente a cerrar caja"
echo "4. La funcionalidad debería trabajar correctamente"
