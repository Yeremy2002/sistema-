# Guía de Prueba: Estadías del Mismo Día

## ✅ Problema Resuelto

**Problema Original:**
- Al seleccionar misma fecha de check-in y check-out, el total mostraba Q. 0
- No se podía confirmar la reserva (rechazada por backend)

**Solución Implementada:**
- Frontend: calcula 1 día cuando noches = 0
- Backend: calcula precio * 1 cuando diffInDays = 0
- Modelo: accessors corregidos para manejar mismo día

---

## 🧪 Pasos de Prueba

### 1. Prueba desde Landing Page

1. Abrir el navegador en: `http://localhost/landing` o tu URL
2. Hacer clic en "Reservar Ahora"
3. **Seleccionar la misma fecha** en:
   - Fecha de llegada: `2025-09-30`
   - Fecha de salida: `2025-09-30`
4. Completar el formulario:
   - Número de huéspedes: `2`
   - Tipo de habitación: Seleccionar cualquiera disponible
   - Nombre completo: `Juan Pérez`
   - Email: `juan@example.com`
   - Teléfono: `(502) 1234-5678`
5. Verificar el **Resumen de Reserva**:
   - ✅ Noches: `1 día (mismo día)`
   - ✅ Precio por noche: `Q. 100`
   - ✅ Total estimado: `Q. 100` ← **DEBE SER 100, NO 0**
6. Hacer clic en **"Confirmar Reserva"**
7. Debe aparecer mensaje de éxito sin errores

---

### 2. Verificar en Base de Datos

```bash
cd /Users/richardortiz/workspace/gestion_hotel/laravel12_migracion
php artisan tinker
```

```php
// Obtener última reserva creada
$reserva = \App\Models\Reserva::latest()->first();

// Verificar datos
echo "Fecha entrada: " . $reserva->fecha_entrada . "\n";
echo "Fecha salida: " . $reserva->fecha_salida . "\n";
echo "Días estancia: " . $reserva->diasEstancia . "\n";
echo "Total: Q. " . $reserva->total . "\n";

// DEBE MOSTRAR:
// Días estancia: 1
// Total: Q. 100.00
```

---

### 3. Verificar Logs

```bash
# Ver logs del backend
tail -f /Users/richardortiz/workspace/gestion_hotel/laravel12_migracion/storage/logs/laravel.log | grep "crear reserva"
```

Buscar líneas como:
```
API crear reserva - Cálculo de total
noches_reales: 0
noches_a_cobrar: 1
total_calculado: 100
```

---

### 4. Prueba con Múltiples Días (Regresión)

Verificar que no rompimos las reservas normales:

1. Seleccionar fechas diferentes:
   - Fecha de llegada: `2025-09-30`
   - Fecha de salida: `2025-10-02` (2 noches)
2. Verificar resumen:
   - ✅ Noches: `2 noches`
   - ✅ Total estimado: `Q. 200`

---

## 🎯 Resultados Esperados

### ✅ Mismo Día (0 noches)
- Noches mostradas: **"1 día (mismo día)"**
- Total calculado: **Q. 100** (precio × 1)
- Reserva creada exitosamente
- Total en BD: **100.00**

### ✅ Múltiples Días (2+ noches)
- Noches mostradas: **"2 noches"**
- Total calculado: **Q. 200** (precio × 2)
- Funciona igual que antes

---

## 🐛 Debugging

Si aún hay problemas:

1. **Abrir consola del navegador** (F12):
   - Buscar errores JavaScript
   - Ver request/response en pestaña Network

2. **Revisar logs Laravel**:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

3. **Verificar base de datos**:
   ```sql
   SELECT id, fecha_entrada, fecha_salida, total, estado 
   FROM reservas 
   ORDER BY id DESC 
   LIMIT 5;
   ```

4. **Limpiar caché**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

---

## 📝 Archivos Modificados

- ✅ `public/landing/script.js` - calculateNights(), updatePriceSummary()
- ✅ `public/landing/styles.css` - Modal optimizado, 2 columnas
- ✅ `app/Http/Controllers/Api/ReservaApiController.php` - crearReserva()
- ✅ `app/Models/Reserva.php` - Accessors getDiasEstanciaAttribute(), getTotalAttribute()

Todos los cambios sincronizados en versiones duplicadas.

---

## 🚀 Deploy a Producción

Antes de hacer push:

```bash
# Verificar cambios
git log --oneline -3

# Hacer push
git push origin main

# En servidor, actualizar:
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**Fecha de corrección:** 2025-09-30
**Commits:**
- 5c2789d: Frontend y UI
- 8174452: Backend y modelo
