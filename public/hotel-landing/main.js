/**
 * Casa Vieja Hotel - Landing Page JavaScript
 * Maneja el menú hamburguesa, modal de reservas y otras interacciones
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades
    initMobileMenu();
    initReservationModal();
    initFloatingCTA();
    initSmoothScroll();
    initPriceCalculator();
});

/**
 * Inicializar menú hamburguesa para móviles
 */
function initMobileMenu() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navClose = document.getElementById('nav-close');
    const navOverlay = document.getElementById('nav-overlay');
    const navLinks = document.querySelectorAll('.nav__link');

    // Verificar que los elementos existen
    if (!navToggle || !navMenu) {
        console.error('❌ Mobile menu elements not found');
        return;
    }
    
    // Abrir menú
    navToggle.addEventListener('click', function(e) {
        e.preventDefault();
        navMenu.classList.add('show-menu');
        document.body.classList.add('menu-open');
    });
    
    // Cerrar menú con botón X
    if (navClose) {
        navClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });
    }
    
    // Cerrar menú con overlay
    if (navOverlay) {
        navOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });
    }
    
    // Cerrar menú al hacer clic en un enlace
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            closeMenu();
        });
    });
    
    // Cerrar menú con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navMenu.classList.contains('show-menu')) {
            closeMenu();
        }
    });
    
    function closeMenu() {
        navMenu.classList.remove('show-menu');
        document.body.classList.remove('menu-open');
    }
}

/**
 * Inicializar modal de reservas
 */
function initReservationModal() {
    const modal = document.getElementById('reservation-modal');
    const openButtons = document.querySelectorAll('.js-open-reservation');
    const closeButtons = document.querySelectorAll('.js-close-modal');

    if (!modal) {
        console.error('❌ Reservation modal not found');
        return;
    }
    
    // Abrir modal
    openButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Pre-llenar datos si el botón tiene información
            const roomType = this.getAttribute('data-room');
            const promoType = this.getAttribute('data-promo');

            if (roomType) {
                const roomSelect = document.getElementById('room-type');
                if (roomSelect) {
                    roomSelect.value = roomType;
                }
            }

            // Mostrar modal
            modal.classList.add('show-modal');
            document.body.classList.add('modal-open');

            // Fallback: forzar estilos directamente si CSS no funciona
            modal.style.display = 'block';
            modal.style.opacity = '1';
            modal.style.visibility = 'visible';
            modal.style.zIndex = '10000';

            // Verificar estilos después del cambio
            setTimeout(() => {
                // Si aún no es visible, intentar con más fuerza
                if (window.getComputedStyle(modal).display === 'none') {
                    modal.setAttribute('style', 'display: block !important; opacity: 1 !important; visibility: visible !important; z-index: 10000 !important; position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(0,0,0,0.6) !important;');
                }

                const firstInput = modal.querySelector('input[type="date"], input[type="text"]');
                if (firstInput) firstInput.focus();
            }, 100);
        });
    });
    
    // Cerrar modal
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    });
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show-modal')) {
            closeModal();
        }
    });
    
    // Manejar envío del formulario
    const form = document.getElementById('reservation-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Reenviar el evento al script original para que maneje la lógica completa
            // El script original (script.js) se encarga de la API, validaciones y SweetAlert2
            if (window.handleReservationSubmit && typeof window.handleReservationSubmit === 'function') {
                window.handleReservationSubmit(e, this);
            } else {
                // Fallback temporal - pero se debe cargar script.js para funcionalidad completa
                const submitButton = this.querySelector('[type="submit"]');
                const originalText = submitButton ? submitButton.textContent : '';
                
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Procesando...';
                }
                
                // Mostrar mensaje temporal
                setTimeout(() => {
                    if (window.showSuccessNotification) {
                        window.showSuccessNotification(
                            'Reserva Enviada',
                            '¡Gracias por tu reserva! Te contactaremos pronto para confirmar los detalles.'
                        );
                    } else {
                        alert('¡Gracias por tu reserva! Te contactaremos pronto para confirmar los detalles.');
                    }
                    
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                    
                    closeModal();
                }, 1000);
            }
        });
    }
    
    function closeModal() {
        modal.classList.remove('show-modal', 'show');
        document.body.classList.remove('modal-open');
        // Force hide with inline styles to ensure it closes
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
        document.body.style.overflow = '';
        console.log('✅ Main.js modal closed');
    }
}

/**
 * Inicializar CTA flotante
 */
function initFloatingCTA() {
    const floatingCTA = document.getElementById('floating-cta');
    const hero = document.querySelector('.hero');
    
    if (!floatingCTA || !hero) return;
    
    window.addEventListener('scroll', function() {
        const heroBottom = hero.offsetTop + hero.offsetHeight;
        const scrollTop = window.pageYOffset;

        if (scrollTop > heroBottom) {
            floatingCTA.classList.add('show');
        } else {
            floatingCTA.classList.remove('show');
        }
    });
}

/**
 * Inicializar smooth scroll
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') {
                e.preventDefault();
                return;
            }
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                
                const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Calculadora de precios para el modal
 */
function initPriceCalculator() {
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const roomTypeSelect = document.getElementById('room-type');
    const priceSummary = document.getElementById('price-summary');
    
    if (!checkinInput || !checkoutInput || !roomTypeSelect || !priceSummary) return;
    
    // Precios por tipo de habitación (por noche)
    const roomPrices = {
        'estandar': 120000,
        'deluxe': 180000,
        'suite': 280000
    };
    
    function calculatePrice() {
        const checkin = new Date(checkinInput.value);
        const checkout = new Date(checkoutInput.value);
        const roomType = roomTypeSelect.value;

        if (!checkin || !checkout || !roomType || checkin >= checkout) {
            priceSummary.style.display = 'none';
            return;
        }

        const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
        const pricePerNight = roomPrices[roomType];

        // Validar que el precio existe
        if (!pricePerNight || isNaN(pricePerNight)) {
            console.warn('Price not found for room type:', roomType);
            priceSummary.style.display = 'none';
            return;
        }

        const total = nights * pricePerNight;

        // Validar que los elementos del DOM existen antes de actualizarlos
        const priceRoomTypeEl = document.getElementById('price-room-type');
        const priceDatesEl = document.getElementById('price-dates');
        const priceNightsEl = document.getElementById('price-nights');
        const pricePerNightEl = document.getElementById('price-per-night');
        const priceTotalEl = document.getElementById('price-total');

        if (priceRoomTypeEl) priceRoomTypeEl.textContent = roomTypeSelect.options[roomTypeSelect.selectedIndex].text;
        if (priceDatesEl) priceDatesEl.textContent = `${checkin.toLocaleDateString()} - ${checkout.toLocaleDateString()}`;
        if (priceNightsEl) priceNightsEl.textContent = nights + (nights === 1 ? ' noche' : ' noches');
        if (pricePerNightEl) pricePerNightEl.textContent = '$' + pricePerNight.toLocaleString();
        if (priceTotalEl) priceTotalEl.textContent = '$' + total.toLocaleString();

        priceSummary.style.display = 'block';
    }
    
    // Event listeners
    checkinInput.addEventListener('change', calculatePrice);
    checkoutInput.addEventListener('change', calculatePrice);
    roomTypeSelect.addEventListener('change', calculatePrice);
    
    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split('T')[0];
    checkinInput.setAttribute('min', today);
    checkoutInput.setAttribute('min', today);
    
    // Actualizar fecha de checkout cuando cambie checkin
    checkinInput.addEventListener('change', function() {
        const checkin = new Date(this.value);
        const minCheckout = new Date(checkin);
        minCheckout.setDate(minCheckout.getDate() + 1);
        checkoutInput.setAttribute('min', minCheckout.toISOString().split('T')[0]);
        
        if (checkoutInput.value && new Date(checkoutInput.value) <= checkin) {
            checkoutInput.value = minCheckout.toISOString().split('T')[0];
        }
    });
}

// Utilidades globales
window.CasaViejaHotel = {
    openReservationModal: function(roomType = null) {
        const modal = document.getElementById('reservation-modal');
        if (modal) {
            if (roomType) {
                const roomSelect = document.getElementById('room-type');
                if (roomSelect) roomSelect.value = roomType;
            }
            modal.classList.add('show-modal');
            document.body.classList.add('modal-open');
        }
    },
    
    closeReservationModal: function() {
        const modal = document.getElementById('reservation-modal');
        if (modal) {
            modal.classList.remove('show-modal', 'show');
            document.body.classList.remove('modal-open');
            // Force hide with inline styles
            modal.style.display = 'none';
            modal.style.opacity = '0';
            modal.style.visibility = 'hidden';
            document.body.style.overflow = '';
            console.log('✅ CasaViejaHotel.closeReservationModal called');
        }
    }
};
