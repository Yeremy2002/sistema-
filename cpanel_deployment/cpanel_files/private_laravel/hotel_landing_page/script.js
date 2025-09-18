// ===== GLOBAL VARIABLES =====
let currentTestimonial = 0;
let isScrolling = false;
let ticking = false;

// ===== DOM ELEMENTS =====
const header = document.getElementById('header');
const navMenu = document.getElementById('nav-menu');
const navToggle = document.getElementById('nav-toggle');
const navClose = document.getElementById('nav-close');
const floatingCta = document.getElementById('floating-cta');
const reservationModal = document.getElementById('reservation-modal');
const testimonialCards = document.querySelectorAll('.testimonial-card');
const testimonialPrev = document.querySelector('.testimonial-btn--prev');
const testimonialNext = document.querySelector('.testimonial-btn--next');

// ===== UTILITY FUNCTIONS =====

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Check if user prefers reduced motion
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// ===== NAVIGATION FUNCTIONS =====

// Show mobile menu
function showMenu() {
    if (navMenu) {
        navMenu.classList.add('show-menu');
        document.body.style.overflow = 'hidden';

        // Focus management for accessibility
        const firstLink = navMenu.querySelector('.nav__link');
        if (firstLink) firstLink.focus();
    }
}

// Hide mobile menu
function hideMenu() {
    if (navMenu) {
        navMenu.classList.remove('show-menu');
        document.body.style.overflow = '';

        // Return focus to toggle button
        if (navToggle) navToggle.focus();
    }
}

// Handle navigation link clicks
function handleNavLinkClick(e) {
    const href = e.target.getAttribute('href');

    if (href && href.startsWith('#')) {
        e.preventDefault();
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
            const headerHeight = header ? header.offsetHeight : 80;
            const targetPosition = targetElement.offsetTop - headerHeight;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            // Close mobile menu if open
            hideMenu();

            // Track navigation for analytics
            trackEvent('navigation', 'click', targetId);
        }
    }
}

// ===== SCROLL EFFECTS =====

// Handle scroll events
function handleScroll() {
    if (!ticking) {
        requestAnimationFrame(() => {
            updateScrollEffects();
            ticking = false;
        });
        ticking = true;
    }
}

// Update scroll-based effects
function updateScrollEffects() {
    const scrollY = window.pageYOffset;
    const windowHeight = window.innerHeight;

    // Header background opacity
    if (header) {
        if (scrollY > 50) {
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
            header.style.backdropFilter = 'blur(15px)';
        } else {
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        }
    }

    // Show/hide floating CTA
    if (floatingCta) {
        if (scrollY > windowHeight * 0.5) {
            floatingCta.classList.add('show');
        } else {
            floatingCta.classList.remove('show');
        }
    }

    // Parallax effects (only if motion is not reduced)
    if (!prefersReducedMotion()) {
        updateParallaxEffects(scrollY);
    }
}

// Update parallax effects
function updateParallaxEffects(scrollY) {
    // Hero background parallax
    const heroBackground = document.querySelector('.parallax-bg');
    if (heroBackground) {
        const speed = parseFloat(heroBackground.dataset.speed) || 0.5;
        const yPos = -(scrollY * speed);
        heroBackground.style.transform = `translateY(${yPos}px)`;
    }

    // Parallax cards
    const parallaxCards = document.querySelectorAll('.parallax-card');
    parallaxCards.forEach(card => {
        const rect = card.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

        if (isVisible) {
            const speed = parseFloat(card.dataset.speed) || 0.1;
            // Calcular el progreso del elemento en la ventana (0 a 1)
            const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (window.innerHeight + rect.height)));
            // Aplicar un movimiento sutil hacia arriba conforme el elemento entra en vista
            const yPos = -(progress * 20 * speed);
            card.style.transform = `translateY(${yPos}px)`;
        }
    });

    // Parallax elements
    const parallaxElements = document.querySelectorAll('.parallax-element');
    parallaxElements.forEach(element => {
        const rect = element.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

        if (isVisible) {
            const speed = parseFloat(element.dataset.speed) || 0.3;
            const yPos = (window.innerHeight - rect.top) * speed * 0.1;
            element.style.transform = `translateY(${yPos}px)`;
        }
    });
}

// ===== TESTIMONIALS CAROUSEL =====

// Show specific testimonial
function showTestimonial(index) {
    testimonialCards.forEach((card, i) => {
        card.classList.toggle('active', i === index);
    });
    currentTestimonial = index;
}

// Next testimonial
function nextTestimonial() {
    const nextIndex = (currentTestimonial + 1) % testimonialCards.length;
    showTestimonial(nextIndex);
    trackEvent('testimonials', 'next', nextIndex);
}

// Previous testimonial
function prevTestimonial() {
    const prevIndex = currentTestimonial === 0 ? testimonialCards.length - 1 : currentTestimonial - 1;
    showTestimonial(prevIndex);
    trackEvent('testimonials', 'prev', prevIndex);
}

// Auto-rotate testimonials
function startTestimonialRotation() {
    setInterval(() => {
        if (!document.hidden) {
            nextTestimonial();
        }
    }, 5000);
}

// ===== RESERVATION MODAL =====

// Open reservation modal
function openReservationModal(packageType = '') {
    if (reservationModal) {
        reservationModal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Pre-fill package type if provided
        if (packageType) {
            const roomTypeSelect = document.getElementById('room-type');
            if (roomTypeSelect) {
                // Map package types to room types
                const packageMap = {
                    'romantico': 'deluxe',
                    'familiar': 'suite',
                    'aventura': 'estandar'
                };
                const roomType = packageMap[packageType] || packageType;
                roomTypeSelect.value = roomType;
            }
        }

        // Set minimum date to today
        const checkinInput = document.getElementById('checkin');
        const checkoutInput = document.getElementById('checkout');
        const today = new Date().toISOString().split('T')[0];

        if (checkinInput) {
            checkinInput.min = today;
            if (!checkinInput.value) {
                checkinInput.value = today;
            }
        }

        if (checkoutInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            checkoutInput.min = tomorrow.toISOString().split('T')[0];
            if (!checkoutInput.value) {
                checkoutInput.value = tomorrow.toISOString().split('T')[0];
            }
        }

        // Focus first input for accessibility
        setTimeout(() => {
            const firstInput = reservationModal.querySelector('input, select');
            if (firstInput) firstInput.focus();
        }, 100);

        // Track modal opening
        trackEvent('reservation', 'modal_open', packageType);
    }
}

// Close reservation modal
function closeReservationModal() {
    if (reservationModal) {
        reservationModal.classList.remove('show');
        document.body.style.overflow = '';

        // Return focus to trigger element
        const triggerBtn = document.querySelector('.floating-cta__btn, .nav__cta');
        if (triggerBtn) triggerBtn.focus();

        // Track modal closing
        trackEvent('reservation', 'modal_close');
    }
}

// Handle reservation form submission with availability check
function handleReservationSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const reservationData = {
        checkin: formData.get('checkin'),
        checkout: formData.get('checkout'),
        guests: formData.get('guests'),
        roomType: formData.get('room-type'),
        guestName: formData.get('guest-name'),
        guestEmail: formData.get('guest-email'),
        guestPhone: formData.get('guest-phone'),
        specialRequests: formData.get('special-requests') || ''
    };
    
    console.log('Form data collected:', reservationData);

    // Validate form data
    if (!validateReservationData(reservationData)) {
        return;
    }

    // Show enhanced loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Add loading spinner and disable form
    submitBtn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> Verificando disponibilidad...';
    submitBtn.disabled = true;
    e.target.querySelectorAll('input, select, textarea').forEach(field => field.disabled = true);

    // Step 1: Check availability first
    checkAvailabilityAndSubmit(reservationData, submitBtn, originalText, e.target);
}

// API Configuration
const API_BASE_URL = 'http://localhost:8001/api';

// Test CORS connectivity
async function testCORS() {
    try {
        console.log('Testing CORS connectivity...');
        const response = await fetch(`${API_BASE_URL}/test-cors`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            mode: 'cors'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('CORS test successful:', data);
        return true;
    } catch (error) {
        console.error('CORS test failed:', error);
        return false;
    }
}

// Call CORS test when page loads
document.addEventListener('DOMContentLoaded', () => {
    testCORS();
});

// Check room availability
async function checkAvailability(params) {
    console.log('checkAvailability called with params:', params);
    try {
        // Build URL with query parameters manually to avoid URL constructor issues
        const baseUrl = `${API_BASE_URL}/reservas/disponibilidad`;
        const queryParams = new URLSearchParams({
            fecha_entrada: params.checkin,
            fecha_salida: params.checkout,
            huespedes: params.guests
        });
        const fullUrl = `${baseUrl}?${queryParams.toString()}`;
        
        console.log('Making request to:', fullUrl);
        
        const response = await fetch(fullUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            mode: 'cors' // Explicitly enable CORS
        });
        
        console.log('Response received:', response);
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        return {
            success: true,
            data: data
        };
    } catch (error) {
        console.error('Detailed error in checkAvailability:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        return {
            success: false,
            error: error.message
        };
    }
}

// Submit reservation to backend
async function submitReservation(reservationData) {
    try {
        const response = await fetch(`${API_BASE_URL}/reservas`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(reservationData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return {
            success: true,
            data: data
        };
    } catch (error) {
        console.error('Error submitting reservation:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

// Check availability and submit reservation
async function checkAvailabilityAndSubmit(reservationData, submitBtn, originalText, form) {
    try {
        // Step 1: Check availability
        submitBtn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> Verificando disponibilidad...';

        const availabilityParams = {
            checkin: reservationData.checkin,
            checkout: reservationData.checkout,
            guests: parseInt(reservationData.guests),
            roomType: reservationData.roomType
        };

        const availabilityResponse = await checkAvailability(availabilityParams);

        if (!availabilityResponse.success || !availabilityResponse.data.habitaciones_disponibles.length) {
            throw new Error('No hay habitaciones disponibles para las fechas seleccionadas. Por favor, elige otras fechas.');
        }

        // Find suitable room from available rooms
        const availableRooms = availabilityResponse.data.habitaciones_disponibles;
        const selectedRoom = findSuitableRoom(availableRooms, reservationData.roomType, reservationData.guests);

        if (!selectedRoom) {
            throw new Error('No hay habitaciones del tipo seleccionado disponibles. Te mostraremos alternativas por WhatsApp.');
        }

        // Step 2: Create reservation with selected room
        submitBtn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> Creando reserva...';

        const reservationPayload = {
            habitacion_id: selectedRoom.id,
            fecha_entrada: reservationData.checkin,
            fecha_salida: reservationData.checkout,
            adelanto: 0, // Will be handled by hotel staff
            cliente_nombre: reservationData.guestName,
            cliente_telefono: reservationData.guestPhone,
            cliente_email: reservationData.guestEmail,
            observaciones: reservationData.specialRequests
        };

        const response = await submitReservation(reservationPayload);

        if (response.success) {
            const message = response.fallback
                ? response.message || '¡Reserva enviada por WhatsApp! Te contactaremos pronto.'
                : `¡Reserva creada exitosamente!

Detalles:
• Habitación: ${selectedRoom.numero} (${selectedRoom.categoria?.nombre || 'N/A'})
• Precio por noche: ${formatPrice(selectedRoom.precio)}
• Total estimado: ${formatPrice(selectedRoom.precio * calculateNights(reservationData.checkin, reservationData.checkout))}

Te contactaremos pronto para confirmar.`;

            showSuccessMessage(message);
            closeReservationModal();
            form.reset();
            trackEvent('reservation', response.fallback ? 'submit_fallback' : 'submit_success');
        } else {
            throw new Error(response.message || 'Error al procesar la reserva');
        }

    } catch (error) {
        console.error('Error in availability check or reservation:', error);

        // If it's an availability issue, offer WhatsApp alternative
        if (error.message.includes('disponibles') || error.message.includes('alternativas')) {
            const message = createWhatsAppMessage(reservationData);
            const whatsappUrl = generateWhatsAppURL(message, 'availability_fallback');
            window.open(whatsappUrl, '_blank');

            showErrorMessage(error.message + '\n\nTe hemos redirigido a WhatsApp para ayudarte con alternativas.');
            closeReservationModal();
            form.reset();
        } else {
            showErrorMessage(error.message || 'Error al verificar disponibilidad. Por favor, intenta nuevamente.');
        }

        trackEvent('reservation', 'availability_error', error.message);
    } finally {
        // Restore form state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        form.querySelectorAll('input, select, textarea').forEach(field => field.disabled = false);
    }
}

// Find suitable room from available rooms
function findSuitableRoom(availableRooms, preferredType, guests) {
    if (!availableRooms || availableRooms.length === 0) {
        return null;
    }

    const guestCount = parseInt(guests);

    // First, try to find exact match by room type/category
    const exactMatch = availableRooms.find(room => {
        const categoryMatch = room.categoria?.nombre?.toLowerCase().includes(preferredType?.toLowerCase()) ||
                            room.tipo?.toLowerCase().includes(preferredType?.toLowerCase());
        const capacityMatch = room.capacidad >= guestCount;
        return categoryMatch && capacityMatch;
    });

    if (exactMatch) return exactMatch;

    // If no exact match, find room with sufficient capacity
    const capacityMatch = availableRooms.find(room => room.capacidad >= guestCount);

    if (capacityMatch) return capacityMatch;

    // If still no match, return first available room (hotel staff will handle)
    return availableRooms[0];
}

// Calculate number of nights
function calculateNights(checkin, checkout) {
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
    return Math.ceil(timeDiff / (1000 * 3600 * 24));
}

// Format price for display (simple version, will use config.js version when available)
function formatPrice(amount) {
    if (typeof window.formatPrice === 'function') {
        return window.formatPrice(amount);
    }
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(amount);
}

// Update room options based on availability
async function updateRoomOptions() {
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const guestsInput = document.getElementById('guests');
    const roomSelect = document.getElementById('room-type');

    if (!checkinInput.value || !checkoutInput.value || !roomSelect) {
        return;
    }

    try {
        // Show loading state in select
        const originalOptions = roomSelect.innerHTML;
        roomSelect.innerHTML = '<option value="">Verificando disponibilidad...</option>';
        roomSelect.disabled = true;

        const availabilityParams = {
            checkin: checkinInput.value,
            checkout: checkoutInput.value,
            guests: parseInt(guestsInput.value) || 2
        };

        const response = await checkAvailability(availabilityParams);
        console.log('Availability response:', response);

        if (response.success && response.data && response.data.habitaciones_disponibles && response.data.habitaciones_disponibles.length > 0) {
            console.log('Found', response.data.habitaciones_disponibles.length, 'available rooms');
            updateRoomSelectOptions(response.data.habitaciones_disponibles, availabilityParams);
        } else {
            console.log('No rooms available or error:', response);
            // No rooms available
            roomSelect.innerHTML = `
                <option value="">No hay habitaciones disponibles</option>
                <option value="consultar">Consultar alternativas por WhatsApp</option>
            `;
            showWarningMessage('No hay habitaciones disponibles para las fechas seleccionadas. Puedes consultar alternativas por WhatsApp.');
        }

    } catch (error) {
        console.error('Error updating room options:', error);
        // Restore original options on error
        roomSelect.innerHTML = `
            <option value="">Seleccionar</option>
            <option value="estandar">Habitación Estándar</option>
            <option value="deluxe">Habitación Deluxe</option>
            <option value="suite">Suite Familiar</option>
        `;
        showWarningMessage('No se pudo verificar disponibilidad. Las opciones mostradas pueden no estar disponibles.');
    } finally {
        roomSelect.disabled = false;
    }
}

// Update room select with available rooms
function updateRoomSelectOptions(availableRooms, params) {
    const roomSelect = document.getElementById('room-type');
    const nights = calculateNights(params.checkin, params.checkout);

    // Group rooms by category for better UX
    const roomsByCategory = {};

    availableRooms.forEach(room => {
        const category = room.categoria?.nombre || 'Habitación';
        if (!roomsByCategory[category]) {
            roomsByCategory[category] = [];
        }
        roomsByCategory[category].push(room);
    });

    // Build options HTML
    let optionsHTML = '<option value="">Seleccionar habitación disponible</option>';

    Object.keys(roomsByCategory).forEach(category => {
        const rooms = roomsByCategory[category];
        const representativeRoom = rooms[0]; // Use first room for category info
        const totalPrice = representativeRoom.precio * nights;
        const priceText = `${formatPrice(representativeRoom.precio)}/noche - Total: ${formatPrice(totalPrice)}`;

        // Create category option
        const categoryKey = category.toLowerCase().replace(/\s+/g, '_');
        optionsHTML += `<option value="${categoryKey}" data-price="${representativeRoom.precio}" data-capacity="${representativeRoom.capacidad || 2}">
            ${category} - ${priceText} (${rooms.length} disponible${rooms.length > 1 ? 's' : ''})
        </option>`;
    });

    roomSelect.innerHTML = optionsHTML;

    // Show success message with availability info
    showSuccessMessage(`Se encontraron ${availableRooms.length} habitaciones disponibles para las fechas seleccionadas.`);
}

// Show warning message
function showWarningMessage(message) {
    showNotification(message, 'warning');
}

// ===== CLIENT SEARCH FUNCTIONS (Phase 3) =====

// Search for existing clients by email or phone
async function searchExistingClient(searchTerm) {
    if (!searchTerm || searchTerm.length < 3) {
        return { success: false, data: null };
    }

    try {
        const response = await searchClient(searchTerm);

        if (response.success && response.data) {
            return {
                success: true,
                data: response.data.cliente || response.data,
                history: response.data.historial_reservas || []
            };
        }

        return { success: false, data: null };

    } catch (error) {
        console.error('Error searching client:', error);
        return { success: false, error: error.message };
    }
}

// Auto-fill client data in the form
function autoFillClientData(clientData) {
    if (!clientData) return;

    const nameInput = document.getElementById('guest-name');
    const emailInput = document.getElementById('guest-email');
    const phoneInput = document.getElementById('guest-phone');

    if (nameInput && clientData.nombre) {
        nameInput.value = clientData.nombre;
        nameInput.classList.add('auto-filled');
    }

    if (emailInput && clientData.email) {
        emailInput.value = clientData.email;
        emailInput.classList.add('auto-filled');
    }

    if (phoneInput && clientData.telefono) {
        phoneInput.value = clientData.telefono;
        phoneInput.classList.add('auto-filled');
    }

    // Show success message
    showSuccessMessage('¡Cliente encontrado! Datos completados automáticamente.');
}

// Show client history
function showClientHistory(clientData, reservations = []) {
    if (!clientData || !reservations.length) return;

    const historyHTML = `
        <div class="client-history">
            <div class="client-history__header">
                <h4><i class="ri-user-line"></i> Cliente frecuente: ${clientData.nombre}</h4>
                <small>${reservations.length} reserva${reservations.length !== 1 ? 's' : ''} anterior${reservations.length !== 1 ? 'es' : ''}</small>
            </div>
            <div class="client-history__list">
                ${reservations.slice(0, 3).map(reservation => `
                    <div class="client-history__item">
                        <span class="history-dates">${formatDate(reservation.fecha_entrada)} - ${formatDate(reservation.fecha_salida)}</span>
                        <span class="history-room">${reservation.habitacion?.numero || 'N/A'} (${reservation.habitacion?.categoria?.nombre || 'N/A'})</span>
                        <span class="history-status status--${reservation.estado?.toLowerCase() || 'desconocido'}">${reservation.estado || 'N/A'}</span>
                    </div>
                `).join('')}
                ${reservations.length > 3 ? `<small class="history-more">+${reservations.length - 3} reserva${reservations.length - 3 !== 1 ? 's' : ''} más</small>` : ''}
            </div>
        </div>
    `;

    // Insert history before the form buttons
    const form = document.getElementById('reservation-form');
    const existingHistory = form.querySelector('.client-history');
    const submitButton = form.querySelector('button[type="submit"]');

    if (existingHistory) {
        existingHistory.remove();
    }

    const historyDiv = document.createElement('div');
    historyDiv.innerHTML = historyHTML;
    form.insertBefore(historyDiv.firstElementChild, submitButton);
}

// Enhanced client lookup on email/phone input
async function handleClientLookup(inputElement, searchTerm) {
    // Clear existing auto-fill styling
    const formInputs = document.querySelectorAll('#reservation-form input');
    formInputs.forEach(input => input.classList.remove('auto-filled'));

    // Remove existing client history
    const existingHistory = document.querySelector('.client-history');
    if (existingHistory) {
        existingHistory.remove();
    }

    if (!searchTerm || searchTerm.length < 5) {
        return;
    }

    // Add loading indicator to input
    inputElement.classList.add('input-loading');

    try {
        const result = await searchExistingClient(searchTerm);

        if (result.success && result.data) {
            autoFillClientData(result.data);
            showClientHistory(result.data, result.history);

            // Track successful client lookup
            trackEvent('client', 'found', searchTerm.includes('@') ? 'email' : 'phone');
        }

    } catch (error) {
        console.error('Client lookup error:', error);
    } finally {
        inputElement.classList.remove('input-loading');
    }
}

// Update price summary
function updatePriceSummary() {
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const roomSelect = document.getElementById('room-type');
    const priceSummary = document.getElementById('price-summary');

    if (!checkinInput.value || !checkoutInput.value || !roomSelect.value || roomSelect.value === 'consultar') {
        priceSummary.style.display = 'none';
        return;
    }

    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const pricePerNight = parseFloat(selectedOption.dataset.price) || 0;
    const nights = calculateNights(checkinInput.value, checkoutInput.value);

    if (pricePerNight === 0) {
        priceSummary.style.display = 'none';
        return;
    }

    // Update summary elements
    document.getElementById('price-room-type').textContent = selectedOption.text.split(' - ')[0];
    document.getElementById('price-dates').textContent = `${formatDate(checkinInput.value)} - ${formatDate(checkoutInput.value)}`;
    document.getElementById('price-nights').textContent = `${nights} noche${nights !== 1 ? 's' : ''}`;
    document.getElementById('price-per-night').textContent = formatPrice(pricePerNight);
    document.getElementById('price-total').textContent = formatPrice(pricePerNight * nights);

    priceSummary.style.display = 'block';
}

// Format date for display
function formatDate(dateString) {
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short'
    };
    return new Date(dateString).toLocaleDateString('es-CO', options);
}

// ===== VALIDATION & SANITIZATION FUNCTIONS =====

// Sanitize string input to prevent XSS
function sanitizeString(str) {
    if (typeof str !== 'string') return str;

    // Create a temporary element to leverage browser's built-in HTML escaping
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#x27;')
        .replace(/\//g, '&#x2F;')
        .trim();
}

// Sanitize email input
function sanitizeEmail(email) {
    if (typeof email !== 'string') return '';
    return email.toLowerCase().trim().replace(/[^\w@.-]/g, '');
}

// Sanitize phone input
function sanitizePhone(phone) {
    if (typeof phone !== 'string') return '';
    return phone.replace(/[^\d\s\-\+\(\)]/g, '').trim();
}

// Enhanced email validation
function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email) && email.length <= 254;
}

// Enhanced phone validation
function validatePhone(phone) {
    // Remove all non-numeric characters for validation
    const numericPhone = phone.replace(/\D/g, '');
    return numericPhone.length >= 7 && numericPhone.length <= 15;
}

// Validate name input
function validateName(name) {
    const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
    return nameRegex.test(name) && name.length >= 2 && name.length <= 100;
}

// Sanitize form data
function sanitizeFormData(formData) {
    const sanitized = {};

    for (const [key, value] of Object.entries(formData)) {
        switch (key) {
            case 'guestEmail':
            case 'email':
                sanitized[key] = sanitizeEmail(value);
                break;
            case 'guestPhone':
            case 'phone':
                sanitized[key] = sanitizePhone(value);
                break;
            case 'guestName':
            case 'name':
            case 'message':
            case 'specialRequests':
                sanitized[key] = sanitizeString(value);
                break;
            default:
                sanitized[key] = typeof value === 'string' ? sanitizeString(value) : value;
        }
    }

    return sanitized;
}

// ===== EXISTING VALIDATION FUNCTIONS (ENHANCED) =====

// Validate reservation data with enhanced security
function validateReservationData(data) {
    // Sanitize data first
    data = sanitizeFormData(data);

    const errors = [];

    // Check required fields
    const requiredFields = ['checkin', 'checkout', 'guests', 'roomType', 'guestName', 'guestEmail', 'guestPhone'];
    requiredFields.forEach(field => {
        if (!data[field] || data[field].trim() === '') {
            errors.push(`El campo ${getFieldLabel(field)} es requerido`);
        }
    });

    // Validate name
    if (data.guestName && !validateName(data.guestName)) {
        errors.push('El nombre solo debe contener letras y espacios');
    }

    // Validate dates
    const checkinDate = new Date(data.checkin);
    const checkoutDate = new Date(data.checkout);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (checkinDate < today) {
        errors.push('La fecha de llegada no puede ser anterior a hoy');
    }

    if (checkoutDate <= checkinDate) {
        errors.push('La fecha de salida debe ser posterior a la fecha de llegada');
    }

    // Enhanced email validation
    if (data.guestEmail && !validateEmail(data.guestEmail)) {
        errors.push('El formato del correo electrónico no es válido');
    }

    // Enhanced phone validation
    if (data.guestPhone && !validatePhone(data.guestPhone)) {
        errors.push('El número de teléfono debe tener entre 7 y 15 dígitos');
    }

    // Validate guests number
    const guestCount = parseInt(data.guests);
    if (isNaN(guestCount) || guestCount < 1 || guestCount > 10) {
        errors.push('El número de huéspedes debe estar entre 1 y 10');
    }

    if (errors.length > 0) {
        showErrorMessage(errors.join('\n'));
        return false;
    }

    return true;
}

// Get field label for validation messages
function getFieldLabel(field) {
    const labels = {
        'checkin': 'Fecha de llegada',
        'checkout': 'Fecha de salida',
        'guests': 'Número de huéspedes',
        'roomType': 'Tipo de habitación',
        'guestName': 'Nombre completo',
        'guestEmail': 'Correo electrónico',
        'guestPhone': 'Teléfono'
    };
    return labels[field] || field;
}

// Submit reservation to API
async function submitReservation(data) {
    try {
        // Map room types to IDs based on the database structure
        const roomTypeMap = {
            'estandar': 1,
            'superior': 7,
            'suite': 13,
            'deluxe': 13 // Map deluxe to suite for now
        };

        const habitacionId = roomTypeMap[data.roomType] || 1;

        // Use the createReservation function from config.js with proper room mapping
        const response = await createReservation({
            habitacion_id: habitacionId,
            fecha_entrada: data.checkin,
            fecha_salida: data.checkout,
            adelanto: 0, // Will be handled by hotel staff
            cliente_nombre: data.guestName,
            cliente_telefono: data.guestPhone,
            cliente_email: data.guestEmail,
            observaciones: data.specialRequests || `Reserva desde landing page - Tipo de habitación: ${data.roomType}`
        });

        return response;

    } catch (error) {
        console.error('API Error:', error);

        // Provide more specific error messages
        if (error.name === 'AbortError') {
            throw new Error('La conexión tardó demasiado. Por favor, intenta nuevamente.');
        } else if (error.message.includes('404')) {
            throw new Error('Servicio no disponible temporalmente. Te contactaremos por WhatsApp.');
        } else if (error.message.includes('500')) {
            throw new Error('Error interno del servidor. Te redirigimos a WhatsApp para completar tu reserva.');
        }

        // Fallback: redirect to WhatsApp with reservation details
        const message = createWhatsAppMessage(data);
        const whatsappUrl = generateWhatsAppURL(message, 'reservation_fallback');
        window.open(whatsappUrl, '_blank');

        return { success: true, fallback: true, message: 'Reserva enviada por WhatsApp' };
    }
}

// Create WhatsApp message with reservation details
function createWhatsAppMessage(data) {
    return `Hola! Me interesa hacer una reserva en Casa Vieja Hotel y Restaurante:

` +
           `📅 Llegada: ${data.checkin}
` +
           `📅 Salida: ${data.checkout}
` +
           `👥 Huéspedes: ${data.guests}
` +
           `🏨 Habitación: ${data.roomType}
` +
           `👤 Nombre: ${data.guestName}
` +
           `📧 Email: ${data.guestEmail}
` +
           `📱 Teléfono: ${data.guestPhone}
` +
           (data.specialRequests ? `📝 Solicitudes: ${data.specialRequests}
` : '') +
           `\n¡Gracias!`;
}

// ===== CONTACT FORM =====

// Handle contact form submission
function handleContactSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const contactData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        message: formData.get('message')
    };

    // Validate contact data
    if (!validateContactData(contactData)) {
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Enviando...';
    submitBtn.disabled = true;

    // Create WhatsApp message
    const whatsappMessage = `Hola! Soy ${contactData.name}.

` +
                           `📧 Email: ${contactData.email}
` +
                           `📱 Teléfono: ${contactData.phone}

` +
                           `Mensaje: ${contactData.message}

` +
                           `Enviado desde la página web.`;

    // Open WhatsApp
    const whatsappUrl = generateWhatsAppURL(whatsappMessage, 'contact_form');
    window.open(whatsappUrl, '_blank');

    // Reset form and show success message
    setTimeout(() => {
        e.target.reset();
        showSuccessMessage('¡Mensaje enviado! Te responderemos pronto por WhatsApp.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        trackEvent('contact', 'form_submit');
    }, 1000);
}

// Validate contact data
// Validate contact data with enhanced security
function validateContactData(data) {
    // Sanitize data first
    data = sanitizeFormData(data);

    const errors = [];

    // Check required fields
    const requiredFields = ['name', 'email', 'phone', 'message'];
    requiredFields.forEach(field => {
        if (!data[field] || data[field].trim() === '') {
            errors.push(`El campo ${getContactFieldLabel(field)} es requerido`);
        }
    });

    // Validate name
    if (data.name && !validateName(data.name)) {
        errors.push('El nombre solo debe contener letras y espacios');
    }

    // Enhanced email validation
    if (data.email && !validateEmail(data.email)) {
        errors.push('El formato del correo electrónico no es válido');
    }

    // Enhanced phone validation
    if (data.phone && !validatePhone(data.phone)) {
        errors.push('El número de teléfono debe tener entre 7 y 15 dígitos');
    }

    // Validate message length
    if (data.message && (data.message.length < 10 || data.message.length > 1000)) {
        errors.push('El mensaje debe tener entre 10 y 1000 caracteres');
    }

    if (errors.length > 0) {
        showErrorMessage(errors.join('\n'));
        return false;
    }

    return true;
}

// Get contact field label
function getContactFieldLabel(field) {
    const labels = {
        'name': 'Nombre',
        'email': 'Correo electrónico',
        'phone': 'Teléfono',
        'message': 'Mensaje'
    };
    return labels[field] || field;
}

// ===== NOTIFICATION SYSTEM =====

// Show success message
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

// Show error message
function showErrorMessage(message) {
    showNotification(message, 'error');
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    notification.innerHTML = `
        <div class="notification__content">
            <p>${message}</p>
            <button class="notification__close" onclick="this.parentElement.parentElement.remove()">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification__content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .notification__content p {
            margin: 0;
            flex: 1;
            white-space: pre-line;
        }
        .notification__close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            line-height: 1;
        }
    `;

    document.head.appendChild(style);
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// ===== ANALYTICS TRACKING =====

// Track events for analytics
function trackEvent(category, action, label = '', value = 0) {
    // Google Analytics 4
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            event_category: category,
            event_label: label,
            value: value
        });
    }

    // Console log for development
    console.log('Event tracked:', { category, action, label, value });
}

// ===== LAZY LOADING =====

// Initialize lazy loading for images with enhanced features
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;

                    // Handle data-src attribute for lazy loading
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }

                    // Handle srcset for responsive images
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                    }

                    // Add loaded class and remove lazy class
                    img.classList.remove('lazy');
                    img.classList.add('loaded');

                    // Error handling for failed image loads
                    img.onerror = function() {
                        img.classList.add('error');
                        console.error('Failed to load image:', img.src);
                    };

                    observer.unobserve(img);
                }
            });
        }, {
            // Load images 50px before they become visible
            rootMargin: '50px 0px',
            threshold: 0.1
        });

        // Observe all lazy images
        const lazyImages = document.querySelectorAll('img[loading="lazy"], img[data-src]');
        lazyImages.forEach(img => {
            img.classList.add('lazy');
            imageObserver.observe(img);
        });

        // Also observe sections for content-visibility optimization
        const lazySections = document.querySelectorAll('.section');
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('lazy-section-visible');
                }
            });
        }, {
            rootMargin: '100px 0px'
        });

        lazySections.forEach(section => {
            section.classList.add('lazy-section');
            sectionObserver.observe(section);
        });

    } else {
        // Fallback for browsers without IntersectionObserver
        const lazyImages = document.querySelectorAll('img[loading="lazy"], img[data-src]');
        lazyImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
            img.classList.remove('lazy');
            img.classList.add('loaded');
        });
    }
}

// ===== KEYBOARD NAVIGATION =====

// Handle keyboard navigation
function handleKeyboardNavigation(e) {
    // Escape key closes modals and menus
    if (e.key === 'Escape') {
        if (reservationModal && reservationModal.classList.contains('show')) {
            closeReservationModal();
        } else if (navMenu && navMenu.classList.contains('show-menu')) {
            hideMenu();
        }
    }

    // Arrow keys for testimonial navigation
    if (e.key === 'ArrowLeft' && document.activeElement.closest('.testimonials')) {
        prevTestimonial();
    } else if (e.key === 'ArrowRight' && document.activeElement.closest('.testimonials')) {
        nextTestimonial();
    }
}

// PWA Install Prompt Management
let deferredPrompt = null;
let isInstallable = false;

// Handle PWA install prompt
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();

    // Stash the event so it can be triggered later
    deferredPrompt = e;
    isInstallable = true;

    console.log('PWA: Install prompt available');

    // Show custom install button after user has engaged with the site
    setTimeout(() => {
        showPWAInstallPrompt();
    }, 30000); // Show after 30 seconds

    trackEvent('pwa', 'install_prompt_available');
});

// Show custom PWA install prompt
function showPWAInstallPrompt() {
    if (!isInstallable || !deferredPrompt) return;

    // Create custom install prompt
    const installBanner = document.createElement('div');
    installBanner.className = 'pwa-install-banner';
    installBanner.innerHTML = `
        <div class="pwa-install-banner__content">
            <div class="pwa-install-banner__text">
                <h4>📱 Instalar Casa Vieja Hotel</h4>
                <p>Accede rápidamente a reservas y ofertas especiales</p>
            </div>
            <div class="pwa-install-banner__actions">
                <button class="btn btn--primary btn--small pwa-install-btn">
                    Instalar
                </button>
                <button class="btn btn--secondary btn--small pwa-dismiss-btn">
                    Ahora no
                </button>
            </div>
        </div>
    `;

    // Add styles
    installBanner.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 16px;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        z-index: 9999;
        animation: slideUp 0.3s ease;
        max-width: 400px;
        margin: 0 auto;
    `;

    document.body.appendChild(installBanner);

    // Handle install button click
    installBanner.querySelector('.pwa-install-btn').addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;

            trackEvent('pwa', 'install_prompt_response', outcome);

            if (outcome === 'accepted') {
                console.log('PWA: User accepted the install prompt');
                showSuccessMessage('¡Gracias! Casa Vieja Hotel se está instalando...');
            } else {
                console.log('PWA: User dismissed the install prompt');
            }

            // Clean up
            deferredPrompt = null;
            isInstallable = false;
            installBanner.remove();
        }
    });

    // Handle dismiss button click
    installBanner.querySelector('.pwa-dismiss-btn').addEventListener('click', () => {
        installBanner.remove();
        trackEvent('pwa', 'install_prompt_dismissed');

        // Don't show again for 7 days
        localStorage.setItem('pwa-install-dismissed', Date.now() + (7 * 24 * 60 * 60 * 1000));
    });

    // Auto-dismiss after 10 seconds
    setTimeout(() => {
        if (installBanner.parentElement) {
            installBanner.remove();
        }
    }, 10000);
}

// Handle successful PWA installation
window.addEventListener('appinstalled', (e) => {
    console.log('PWA: App was installed successfully');
    showSuccessMessage('¡Casa Vieja Hotel instalada exitosamente! 🎉');
    trackEvent('pwa', 'app_installed');

    // Clean up
    deferredPrompt = null;
    isInstallable = false;
});

// Check if app is running as PWA
function isPWA() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
}

// PWA-specific functionality
if (isPWA()) {
    console.log('PWA: Running as installed PWA');
    trackEvent('pwa', 'running_as_pwa');

    // Hide browser-specific elements
    document.body.classList.add('pwa-mode');

    // Add PWA-specific styles
    const pwaStyles = `
        .pwa-mode .nav__cta {
            display: inline-flex !important;
        }

        .pwa-mode .floating-cta {
            bottom: 30px;
        }

        .pwa-mode .whatsapp-float {
            bottom: 30px;
        }
    `;

    const style = document.createElement('style');
    style.textContent = pwaStyles;
    document.head.appendChild(style);
}

// ===== INITIALIZATION =====

// Initialize all functionality when DOM is loaded
function init() {
    // Navigation event listeners
    if (navToggle) {
        navToggle.addEventListener('click', showMenu);
    }

    if (navClose) {
        navClose.addEventListener('click', hideMenu);
    }

    // Navigation links
    const navLinks = document.querySelectorAll('.nav__link, .hero__scroll-link');
    navLinks.forEach(link => {
        link.addEventListener('click', handleNavLinkClick);
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navMenu && navMenu.classList.contains('show-menu') &&
            !navMenu.contains(e.target) && !navToggle.contains(e.target)) {
            hideMenu();
        }
    });

    // Scroll event listener
    window.addEventListener('scroll', throttle(handleScroll, 16));

    // Testimonial controls
    if (testimonialPrev) {
        testimonialPrev.addEventListener('click', prevTestimonial);
    }

    if (testimonialNext) {
        testimonialNext.addEventListener('click', nextTestimonial);
    }

    // Start testimonial rotation
    if (testimonialCards.length > 0) {
        startTestimonialRotation();
    }

    // Reservation form
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.addEventListener('submit', handleReservationSubmit);
    }

    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
    }

    // Enhanced date input validation with availability check
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const guestsInput = document.getElementById('guests');
    const roomSelect = document.getElementById('room-type');

    // Client lookup inputs
    const emailInput = document.getElementById('guest-email');
    const phoneInput = document.getElementById('guest-phone');

    // Debounced functions
    const debouncedUpdateRooms = debounce(updateRoomOptions, 1000);
    const debouncedUpdatePrice = debounce(updatePriceSummary, 500);
    const debouncedClientLookup = debounce(handleClientLookup, 1500);

    // Client lookup event listeners
    if (emailInput) {
        emailInput.addEventListener('input', (e) => {
            const email = e.target.value.trim();
            if (email.includes('@') && email.length > 5) {
                debouncedClientLookup(e.target, email);
            }
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            const phone = e.target.value.trim();
            if (phone.length > 7) {
                debouncedClientLookup(e.target, phone);
            }
        });
    }

    if (checkinInput) {
        checkinInput.addEventListener('change', (e) => {
            if (checkoutInput) {
                const checkinDate = new Date(e.target.value);
                const minCheckout = new Date(checkinDate);
                minCheckout.setDate(minCheckout.getDate() + 1);
                checkoutInput.min = minCheckout.toISOString().split('T')[0];

                if (checkoutInput.value && new Date(checkoutInput.value) <= checkinDate) {
                    checkoutInput.value = minCheckout.toISOString().split('T')[0];
                }

                // Update room options based on new dates
                if (checkoutInput.value) {
                    debouncedUpdateRooms();
                }
            }
        });
    }

    if (checkoutInput) {
        checkoutInput.addEventListener('change', (e) => {
            // Update room options when checkout date changes
            if (checkinInput.value) {
                debouncedUpdateRooms();
            }
        });
    }

    if (guestsInput) {
        guestsInput.addEventListener('change', (e) => {
            // Update room options when guest count changes
            if (checkinInput.value && checkoutInput.value) {
                debouncedUpdateRooms();
            }
        });
    }

    if (roomSelect) {
        roomSelect.addEventListener('change', (e) => {
            // Update price summary when room selection changes
            debouncedUpdatePrice();

            // Handle "consultar" option
            if (e.target.value === 'consultar') {
                const message = `Hola! Estoy buscando disponibilidad para:

📅 Llegada: ${checkinInput.value || 'A definir'}
📅 Salida: ${checkoutInput.value || 'A definir'}
👥 Huéspedes: ${guestsInput.value || '2'}

¿Podrían ayudarme con alternativas disponibles?`;

                const whatsappUrl = generateWhatsAppURL(message, 'availability_inquiry');
                window.open(whatsappUrl, '_blank');

                trackEvent('reservation', 'whatsapp_consultation');
            }
        });
    }

    // New event listeners for buttons with JS classes (replacing inline handlers)
    const openReservationBtns = document.querySelectorAll('.js-open-reservation');
    openReservationBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const roomType = btn.dataset.room || btn.dataset.promo || null;
            openReservationModal(roomType);
        });
    });

    const closeModalBtns = document.querySelectorAll('.js-close-modal');
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeReservationModal();
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', handleKeyboardNavigation);

    // Initialize lazy loading
    initLazyLoading();

    // Initial scroll effects
    handleScroll();

    // Track page load
    trackEvent('page', 'load', window.location.pathname);

    console.log('Casa Vieja Hotel Landing Page initialized successfully!');
}

// ===== GLOBAL FUNCTIONS (for HTML onclick handlers) =====
window.openReservationModal = openReservationModal;
window.closeReservationModal = closeReservationModal;
window.showMenu = showMenu;
window.hideMenu = hideMenu;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Pause animations when page is not visible
        document.body.classList.add('page-hidden');
    } else {
        // Resume animations when page becomes visible
        document.body.classList.remove('page-hidden');
    }
});

// Handle resize events
window.addEventListener('resize', debounce(() => {
    // Recalculate parallax effects on resize
    if (!prefersReducedMotion()) {
        handleScroll();
    }
}, 250));

// Service Worker registration (for PWA capabilities)
// Commented out since sw.js file doesn't exist
/*
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
*/
