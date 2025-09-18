#!/bin/bash

# Script de validación para deployment en cPanel
# Verifica que todos los archivos necesarios estén presentes

echo "🔍 Validando deployment para cPanel..."
echo "========================================="

ERRORS=0
WARNINGS=0

# Función para reportar errores
error() {
    echo "❌ ERROR: $1"
    ((ERRORS++))
}

# Función para reportar advertencias
warning() {
    echo "⚠️  WARNING: $1"
    ((WARNINGS++))
}

# Función para reportar éxito
success() {
    echo "✅ $1"
}

echo ""
echo "📁 Verificando estructura de archivos..."

# Verificar archivos principales
if [ -f "README_CPANEL_DEPLOYMENT.md" ]; then
    success "README principal encontrado"
else
    error "README_CPANEL_DEPLOYMENT.md faltante"
fi

if [ -f "QUICK_START_GUIDE.md" ]; then
    success "Guía rápida encontrada"
else
    error "QUICK_START_GUIDE.md faltante"
fi

if [ -f "DEPLOYMENT_SUMMARY.md" ]; then
    success "Resumen de deployment encontrado"
else
    error "DEPLOYMENT_SUMMARY.md faltante"
fi

# Verificar script de preparación
if [ -f "prepare_for_cpanel.sh" ] && [ -x "prepare_for_cpanel.sh" ]; then
    success "Script de preparación ejecutable"
else
    error "prepare_for_cpanel.sh faltante o no ejecutable"
fi

echo ""
echo "📊 Verificando base de datos..."

# Verificar archivos de base de datos
if [ -f "database/hotel_management.sql" ]; then
    size=$(wc -c < "database/hotel_management.sql")
    if [ $size -gt 1000 ]; then
        success "Archivo SQL principal (${size} bytes)"
    else
        warning "Archivo SQL muy pequeño (${size} bytes)"
    fi
else
    error "database/hotel_management.sql faltante"
fi

if [ -f "database/create_database.sql" ]; then
    success "Script de creación de BD encontrado"
else
    error "database/create_database.sql faltante"
fi

echo ""
echo "📜 Verificando scripts..."

# Verificar scripts
scripts_required=(
    "scripts/export_database.php"
    "scripts/post_deploy.php"
    "scripts/optimize_for_shared_hosting.php"
)

for script in "${scripts_required[@]}"; do
    if [ -f "$script" ]; then
        success "Script: $(basename $script)"
    else
        error "Script faltante: $script"
    fi
done

echo ""
echo "📚 Verificando documentación..."

# Verificar documentación
docs_required=(
    "docs/INSTALLATION_STEPS.md"
    "docs/CRONJOBS_SETUP.md"
    "docs/TROUBLESHOOTING.md"
)

for doc in "${docs_required[@]}"; do
    if [ -f "$doc" ]; then
        success "Documentación: $(basename $doc)"
    else
        error "Documentación faltante: $doc"
    fi
done

echo ""
echo "⚙️  Verificando archivos de configuración..."

# Verificar configuraciones
if [ -f "config/.env.cpanel" ]; then
    success "Configuración .env para cPanel"
else
    error "config/.env.cpanel faltante"
fi

echo ""
echo "📦 Verificando archivos preparados..."

# Verificar estructura de archivos preparados
if [ -d "cpanel_files" ]; then
    success "Directorio cpanel_files encontrado"

    if [ -d "cpanel_files/public_html" ]; then
        public_files=$(find cpanel_files/public_html -type f | wc -l)
        success "Archivos públicos preparados ($public_files archivos)"

        # Verificar archivos críticos en public_html
        if [ -f "cpanel_files/public_html/index.php" ]; then
            success "index.php modificado para cPanel"
        else
            error "index.php faltante en public_html"
        fi

        if [ -f "cpanel_files/public_html/.htaccess" ]; then
            success ".htaccess configurado"
        else
            error ".htaccess faltante en public_html"
        fi
    else
        error "cpanel_files/public_html faltante"
    fi

    if [ -d "cpanel_files/private_laravel" ]; then
        laravel_files=$(find cpanel_files/private_laravel -type f | wc -l)
        success "Aplicación Laravel preparada ($laravel_files archivos)"

        # Verificar archivos críticos de Laravel
        if [ -f "cpanel_files/private_laravel/artisan" ]; then
            success "artisan encontrado"
        else
            error "artisan faltante en private_laravel"
        fi

        if [ -d "cpanel_files/private_laravel/vendor" ]; then
            success "Dependencias vendor incluidas"
        else
            warning "Directorio vendor no encontrado (requerirá composer install)"
        fi

        if [ -f "cpanel_files/private_laravel/.env" ]; then
            success "Archivo .env incluido"
        else
            warning "Archivo .env no incluido (se copiará de .env.example)"
        fi
    else
        error "cpanel_files/private_laravel faltante"
    fi
else
    error "Directorio cpanel_files faltante - ejecutar prepare_for_cpanel.sh"
fi

echo ""
echo "🔐 Verificando seguridad..."

# Verificar que archivos sensibles no estén en public_html
sensitive_files=(
    ".env"
    "artisan"
    "composer.json"
    "composer.lock"
)

for file in "${sensitive_files[@]}"; do
    if [ -f "cpanel_files/public_html/$file" ]; then
        error "Archivo sensible en public_html: $file"
    else
        success "Archivo sensible protegido: $file"
    fi
done

echo ""
echo "📋 Verificando integridad de archivos..."

# Verificar que index.php apunte correctamente
if [ -f "cpanel_files/public_html/index.php" ]; then
    if grep -q "private_laravel" "cpanel_files/public_html/index.php"; then
        success "index.php apunta a private_laravel"
    else
        error "index.php no configurado correctamente"
    fi
fi

# Verificar symlink de storage
if [ -L "cpanel_files/public_html/storage" ]; then
    success "Symlink de storage configurado"
else
    warning "Symlink de storage no encontrado (se creará durante deployment)"
fi

echo ""
echo "========================================="
echo "📊 RESUMEN DE VALIDACIÓN"
echo "========================================="

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo "🎉 ¡PERFECTO! Deployment completamente preparado"
    echo ""
    echo "✅ Todos los archivos están presentes"
    echo "✅ Configuraciones correctas"
    echo "✅ Estructura de seguridad válida"
    echo ""
    echo "📤 LISTO PARA SUBIR A CPANEL"
    echo ""
    echo "Siguiente paso:"
    echo "1. Crear base de datos en cPanel"
    echo "2. Subir archivos según documentación"
    echo "3. Configurar variables de entorno"
    echo "4. Configurar cronjobs"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo "⚠️  ADVERTENCIAS ENCONTRADAS: $WARNINGS"
    echo ""
    echo "El deployment puede proceder, pero revisa las advertencias."
    echo "Consulta la documentación para optimizar el proceso."
    exit 0
else
    echo "❌ ERRORES ENCONTRADOS: $ERRORS"
    echo "⚠️  ADVERTENCIAS: $WARNINGS"
    echo ""
    echo "🚫 NO PROCEDER CON EL DEPLOYMENT"
    echo ""
    echo "Soluciones:"
    echo "1. Ejecutar: ./prepare_for_cpanel.sh"
    echo "2. Verificar que todos los archivos estén presentes"
    echo "3. Ejecutar validación nuevamente"
    echo ""
    echo "Para soporte, consultar: docs/TROUBLESHOOTING.md"
    exit 1
fi