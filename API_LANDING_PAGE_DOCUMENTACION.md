# 📡 API REST - Sistema de Gestión Hotelera

## 🎯 Resumen Ejecutivo

La API REST está **100% LISTA** para integrarse con la landing page. Se han implementado todos los endpoints necesarios con validaciones diferenciadas para landing page vs backend administrativo.

## 🌐 Arquitectura API

### **Landing Page** (Rutas públicas)

-   **Base URL**: `http://localhost:8001/api/`
-   **CORS**: Habilitado ✅
-   **Autenticación**: No requerida ✅
-   **Validaciones**: Permisivas (experiencia de usuario) ✅

### **Backend Administrativo** (Rutas web)

-   **Base URL**: `http://localhost:8001/`
-   **CSRF**: Requerido ✅
-   **Autenticación**: Obligatoria ✅
-   **Validaciones**: Estrictas (completitud de datos) ✅

---

## 📋 Endpoints Disponibles

### 🏨 **RESERVAS**

#### `GET /api/reservas/calendario`

**Propósito**: Obtener eventos del calendario de reservas  
**Uso**: Mostrar reservas existentes en la landing page

```bash
curl -X GET "http://localhost:8001/api/reservas/calendario" \
  -H "Accept: application/json"
```

**Respuesta exitosa:**

```json
[
    {
        "id": 1,
        "title": "Hab. 111 - Check-in",
        "start": "2025-08-10T14:00:00",
        "end": "2025-08-12T12:00:00",
        "backgroundColor": "#dc3545",
        "extendedProps": {
            "estado": "Check-in",
            "habitacion_numero": "111",
            "cliente_nombre": "MARIA RODRIGUEZ",
            "precio": "100.00"
        }
    }
]
```

---

#### `GET /api/reservas/disponibilidad`

**Propósito**: Consultar habitaciones disponibles  
**Uso**: Motor de búsqueda de la landing page

**Parámetros requeridos:**

-   `fecha_entrada` (date, after_or_equal:today)
-   `fecha_salida` (date, after:fecha_entrada)

**Parámetros opcionales:**

-   `categoria_id` (integer, exists:categorias,id)
-   `nivel_id` (integer, exists:nivels,id)

```bash
curl -X GET "http://localhost:8001/api/reservas/disponibilidad?fecha_entrada=2025-08-15&fecha_salida=2025-08-17" \
  -H "Accept: application/json"
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "data": {
        "habitaciones_disponibles": [
            {
                "id": 1,
                "numero": "111",
                "precio": "100.00",
                "categoria": {
                    "nombre": "Estandar",
                    "descripcion": "Habitación estándar con cama matrimonial"
                },
                "nivel": {
                    "nombre": "Planta Baja"
                }
            }
        ],
        "total_disponibles": 18,
        "fecha_entrada": "2025-08-15",
        "fecha_salida": "2025-08-17"
    }
}
```

---

#### `POST /api/reservas`

**Propósito**: Crear nueva reserva desde landing page  
**Uso**: Proceso de reserva rápida

**Campos requeridos (Landing Page):**

```json
{
    "habitacion_id": 1,
    "fecha_entrada": "2025-08-15",
    "fecha_salida": "2025-08-17",
    "adelanto": 100.0,
    "cliente_nombre": "Maria Rodriguez",
    "cliente_telefono": "+502 5555-1234"
}
```

**Campos opcionales:**

```json
{
    "observaciones": "Reserva desde landing page",
    "cliente_email": "maria@example.com",
    "cliente_documento": "12345678",
    "cliente_direccion": "Ciudad de Guatemala",
    "cliente_nit": "12345678K",
    "cliente_dpi": "1234567890123"
}
```

```bash
curl -X POST "http://localhost:8001/api/reservas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "habitacion_id": 1,
    "fecha_entrada": "2025-08-15",
    "fecha_salida": "2025-08-17",
    "adelanto": 100.00,
    "cliente_nombre": "Maria Rodriguez",
    "cliente_telefono": "+502 5555-1234",
    "cliente_email": "maria@example.com"
  }'
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Reserva creada exitosamente. Debe ser confirmada dentro de 1 hora.",
    "data": {
        "reserva_id": 11,
        "numero_reserva": 11,
        "estado": "Pendiente de Confirmación",
        "total": 200,
        "adelanto": 100,
        "pendiente": 100,
        "expires_at": "2025-08-08 13:56:23",
        "habitacion": {
            "numero": "111",
            "categoria": "Estandar",
            "precio": "100.00"
        },
        "cliente": {
            "id": 6,
            "nombre": "MARIA RODRIGUEZ"
        }
    }
}
```

---

### 👤 **CLIENTES**

#### `GET /api/clientes/buscar`

**Propósito**: Búsqueda general de clientes  
**Uso**: Autocompletado en formularios

**Parámetros:**

-   `q` (string): Término de búsqueda (nombre, NIT, DPI)

```bash
curl -X GET "http://localhost:8001/api/clientes/buscar?q=Maria" \
  -H "Accept: application/json"
```

---

#### `GET /api/clientes/buscar-por-dpi/{dpi}`

**Propósito**: Búsqueda específica por DPI  
**Uso**: Validación de cliente existente

```bash
curl -X GET "http://localhost:8001/api/clientes/buscar-por-dpi/1234567890123" \
  -H "Accept: application/json"
```

---

#### `GET /api/clientes/buscar-por-nit/{nit}`

**Propósito**: Búsqueda específica por NIT  
**Uso**: Validación de cliente existente

```bash
curl -X GET "http://localhost:8001/api/clientes/buscar-por-nit/12345678K" \
  -H "Accept: application/json"
```

---

## 🔄 Flujo de Integración Landing Page

### **1. Consulta de Disponibilidad**

```javascript
const disponibilidad = await fetch(
    `/api/reservas/disponibilidad?fecha_entrada=${fechaEntrada}&fecha_salida=${fechaSalida}`
).then((res) => res.json());
```

### **2. Creación de Reserva**

```javascript
const reserva = await fetch("/api/reservas", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        habitacion_id: habitacionSeleccionada,
        fecha_entrada: "2025-08-15",
        fecha_salida: "2025-08-17",
        adelanto: 100.0,
        cliente_nombre: "Maria Rodriguez",
        cliente_telefono: "+502 5555-1234",
        cliente_email: "maria@example.com",
    }),
}).then((res) => res.json());
```

### **3. Búsqueda de Cliente (Opcional)**

```javascript
const clienteExistente = await fetch(
    `/api/clientes/buscar-por-dpi/${dpi}`
).then((res) => res.json());
```

---

## ✅ Estado de la API

| Funcionalidad                  | Estado | Notas                                   |
| ------------------------------ | ------ | --------------------------------------- |
| **CORS configurado**           | ✅     | Permite requests desde cualquier origen |
| **Rutas API públicas**         | ✅     | Sin autenticación requerida             |
| **Validación diferenciada**    | ✅     | Landing page vs Backend                 |
| **Gestión de clientes nuevos** | ✅     | NIT/DPI opcionales para landing         |
| **Consulta disponibilidad**    | ✅     | Filtros por categoría y nivel           |
| **Creación de reservas**       | ✅     | Expiración automática en 1 hora         |
| **Búsqueda de clientes**       | ✅     | Por término general, DPI o NIT          |
| **Manejo de errores**          | ✅     | Respuestas JSON estructuradas           |

---

## 🛠️ Configuración Técnica Aplicada

### **Base de Datos**

-   ✅ Campos NIT y DPI ahora son **nullable**
-   ✅ Campo `origen` diferencia clientes de landing vs backend
-   ✅ Campos adicionales: `email`, `direccion`, `documento`
-   ✅ Índices únicos condicionales (solo para valores no nulos)

### **Validaciones**

-   ✅ Landing page: solo nombre y teléfono requeridos
-   ✅ Backend: NIT y DPI obligatorios
-   ✅ Experiencia de usuario optimizada para landing

### **Seguridad**

-   ✅ CORS habilitado para API
-   ✅ Rutas públicas separadas de rutas autenticadas
-   ✅ Validación de disponibilidad antes de crear reservas
-   ✅ Expiración automática de reservas pendientes

---

## 🚀 Listo para Producción

La API está **100% lista** para integrarse con la landing page. Todos los endpoints han sido probados y funcionan correctamente. La diferenciación entre landing page y backend administrativo está implementada y operativa.

**Próximos pasos sugeridos:**

1. ✅ Implementar la landing page consumiendo estos endpoints
2. ✅ Configurar notificaciones para reservas pendientes
3. ✅ Implementar sistema de confirmación de reservas
4. ✅ Agregar métricas y logging para monitoreo
