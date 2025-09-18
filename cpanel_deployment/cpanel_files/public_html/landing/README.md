# Casa Vieja Hotel y Restaurante - Landing Page

Landing page moderna y elegante para Casa Vieja Hotel y Restaurante, un hotel de estilo rústico ubicado en el corazón de la montaña. La página utiliza efectos de parallax sutiles y está optimizada para conversiones directas.

## 🎯 Objetivo

Incrementar reservas directas y consultas por WhatsApp, mostrando la propuesta de valor del hotel (descanso rústico, gastronomía local, vistas a la montaña) con navegación rápida y clara.

## 🎨 Paleta de Colores

Basada en los colores del logo del hotel, transmitiendo calidez y confort:

- **Primario (Naranja)**: `#DC8711` - CTAs y elementos destacados
- **Secundario (Marrón Oscuro)**: `#664D07` - Títulos e íconos
- **Arena Medio**: `#BEA572` - Elementos decorativos
- **Oliva/Mostaza**: `#8B7A47` - Textos secundarios
- **Beige Claro**: `#DDCAA4` - Fondos suaves
- **Crema Muy Claro**: `#EDE9DA` - Fondos principales

## 🏗️ Estructura del Proyecto

```
hotel_landing_page/
├── index.html              # Página principal
├── styles.css              # Estilos CSS
├── script.js               # JavaScript funcional
├── config.js               # Configuración API y WhatsApp
├── README.md               # Documentación
├── images/                 # Imágenes SVG optimizadas
│   ├── logo.svg           # Logo del hotel
│   ├── hero-mountain.svg  # Imagen hero con montañas
│   ├── room-standard.svg  # Habitación estándar
│   ├── room-deluxe.svg    # Habitación deluxe
│   ├── room-suite.svg     # Suite familiar
│   ├── restaurant.svg     # Restaurante
│   ├── activities.svg     # Actividades al aire libre
│   └── panoramic-view.svg # Vista panorámica
└── manifest.json          # PWA manifest
```

## 📱 Secciones de la Landing Page

### Header
- Logo a la izquierda
- Menú de navegación con anclas
- CTA "Reserva Ya" prominente

### Hero (con Parallax)
- Imagen de fondo con montañas
- Título principal: "Tu hogar en el corazón de la montaña"
- Subtítulo con beneficio clave
- CTAs primario y secundario

### Promociones
- Tarjetas con efectos parallax
- Ofertas especiales con precios
- Fechas de validez

### Habitaciones
- Cards con fotos, amenities y precios
- Botones de reserva directa
- Información de ocupación

### Restaurante
- Mini-menú destacado
- Horarios de servicio
- Plato insignia

### Experiencias
- Tours locales y actividades
- Senderismo y fogatas
- Iconografía rústica

### Galería
- Grid masonry con lazy-loading
- Imágenes optimizadas

### Opiniones
- Carrusel de testimonios
- Rating promedio
- Reseñas destacadas

### Ubicación
- Mapa embebido
- Instrucciones de llegada
- Información de estacionamiento

### Contacto
- Formulario de contacto
- Integración con WhatsApp
- Información de contacto

### Footer
- Datos legales
- Redes sociales
- Políticas y derechos

## 🚀 Características Técnicas

### Performance
- **LCP**: < 2.5s (móvil)
- **CLS**: < 0.1
- **TBT**: Bajo
- Imágenes WebP/AVIF con srcset
- Lazy loading implementado

### Parallax
- Efectos sutiles (translateY 10-40px)
- Deshabilitado en iOS si afecta performance
- Respeta `prefers-reduced-motion`
- Usa IntersectionObserver para optimización

### Accesibilidad
- Contraste AA/AAA (WCAG 2.1)
- Alt descriptivos en imágenes
- Orden de tabulación lógico
- Aria-labels en CTAs
- Idioma definido (lang="es")

### SEO
- Título < 60 caracteres
- Meta descripción < 155 caracteres
- URLs limpias
- Schema.org/Hotel implementado
- Open Graph tags

### PWA
- Service Worker registrado
- Manifest.json configurado
- Funcionalidad offline básica

## 🔧 Configuración de API

El archivo `config.js` contiene la configuración para integrar con el backend Laravel:

```javascript
const API_CONFIG = {
    BASE_URL: 'http://localhost:8001/api',
    ENDPOINTS: {
        DISPONIBILIDAD: '/reservas/disponibilidad',
        CREAR_RESERVA: '/reservas',
        BUSCAR_CLIENTE: '/clientes/buscar'
    }
};
```

### Funciones Disponibles
- `checkAvailability(params)` - Verificar disponibilidad
- `createReservation(data)` - Crear reserva
- `searchClient(term)` - Buscar cliente existente
- `generateWhatsAppURL(message)` - Generar enlace WhatsApp

## 📱 WhatsApp Integration

Configuración de WhatsApp para consultas directas:

```javascript
const WHATSAPP_CONFIG = {
    PHONE_NUMBER: '57XXXXXXXXX', // Reemplazar con número real
    MESSAGES: {
        GENERAL_INQUIRY: 'Hola! Me interesa información sobre Casa Vieja Hotel...',
        RESERVATION_INQUIRY: 'Hola! Me gustaría hacer una reserva...',
        CONTACT_FORM: 'Hola! Te escribo desde la página web...'
    }
};
```

## 🎨 Guía de Estilos

### Tipografías
- **Títulos**: Playfair Display (serif cálida)
- **Textos**: Inter (sans-serif legible)
- **Fallbacks**: serif, sans-serif seguros

### Componentes UI

#### Botones
- **Primario**: Fondo #DC8711, texto blanco
- **Secundario**: Borde #DC8711, texto #DC8711
- **Estados**: hover, focus, active definidos

#### Cards
- Fondo #EDE9DA
- Bordes sutiles #BEA572
- Sombras suaves
- Efectos parallax en hover

#### Formularios
- Campos con bordes #BEA572
- Focus en #DC8711
- Validación visual clara
- Mensajes de error/éxito

### Efectos Parallax
- **Hero**: Fondo se mueve más lento que contenido
- **Cards**: Traslación sutil en scroll (10-20px)
- **Imágenes**: Efecto de profundidad
- **Desactivación**: Automática con `prefers-reduced-motion`

## 📊 Analytics

Eventos de seguimiento configurados:
- Clics en "Reserva Ya"
- Clics en WhatsApp
- Envío de formularios
- Navegación entre secciones

## 🔄 Estados de Carga

### Reservas
- Loading spinner durante verificación
- Mensajes de éxito/error claros
- Fallback a WhatsApp si API falla

### Imágenes
- Placeholders durante carga
- Lazy loading implementado
- Fallbacks para errores

## 🚀 Deployment

### Requisitos
- Servidor web (Apache/Nginx)
- HTTPS habilitado
- Compresión gzip/brotli

### Optimizaciones
1. Minificar CSS/JS
2. Comprimir imágenes
3. Configurar cache headers
4. Habilitar compresión
5. CDN para assets estáticos

## 🔧 Desarrollo Local

1. Clonar el repositorio
2. Abrir `index.html` en navegador
3. Para desarrollo con servidor local:
   ```bash
   python -m http.server 8000
   # o
   npx serve .
   ```

## 📝 Personalización

### Cambiar Colores
Modificar variables CSS en `styles.css`:
```css
:root {
    --primary-color: #DC8711;
    --secondary-color: #664D07;
    /* ... */
}
```

### Actualizar Contenido
- Textos: Editar directamente en `index.html`
- Imágenes: Reemplazar archivos SVG en `/images/`
- Configuración: Modificar `config.js`

### Agregar Secciones
1. Añadir HTML en `index.html`
2. Estilos en `styles.css`
3. Funcionalidad en `script.js`
4. Actualizar navegación

## 🐛 Troubleshooting

### Problemas Comunes

**Parallax no funciona en móvil**
- Verificar que no esté deshabilitado por `prefers-reduced-motion`
- Comprobar soporte del navegador

**API no responde**
- Verificar URL en `config.js`
- Comprobar CORS en backend
- Revisar console para errores

**WhatsApp no abre**
- Verificar formato del número de teléfono
- Comprobar encoding del mensaje

## 📞 Soporte

Para soporte técnico o consultas sobre el proyecto:
- Email: desarrollo@casaviejahotel.com
- WhatsApp: +57 (8) 123-4567

## 📄 Licencia

© 2024 Casa Vieja Hotel y Restaurante. Todos los derechos reservados.

---

**Versión**: 1.0.0  
**Última actualización**: Diciembre 2024  
**Compatibilidad**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+