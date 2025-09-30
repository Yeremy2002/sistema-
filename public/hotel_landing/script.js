// ===== GLOBAL VARIABLES =====
let currentTestimonial = 0;
let isScrolling = false;
let ticking = false;

// ===== DOM ELEMENTS =====
let header, navMenu, navToggle, navClose, navOverlay, floatingCta, reservationModal, testimonialCards, testimonialPrev, testimonialNext;

// Initialize DOM elements after page load
function initializeDOMElements() {
    header = document.getElementById('header');
    navMenu = document.getElementById('nav-menu');
    navToggle = document.getElementById('nav-toggle');
    navClose = document.getElementById('nav-close');
    navOverlay = document.getElementById('nav-overlay');
    floatingCta = document.getElementById('floating-cta');
    reservationModal = document.getElementById('reservation-modal');
    testimonialCards = document.querySelectorAll('.testimonial-card');
    testimonialPrev = document.querySelector('.testimonial-btn--prev');
    testimonialNext = document.querySelector('.testimonial-btn--next');
}

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
        document.body.classList.add('nav-open');
        document.body.style.overflow = 'hidden';
        
        // Show overlay
        if (navOverlay) {
            navOverlay.classList.add('active');
        }

        // Focus management for accessibility
        const firstLink = navMenu.querySelector('.nav__link');
        if (firstLink) firstLink.focus();
        
        // Track menu open event
        trackEvent('navigation', 'menu_open');
    }
}

// Hide mobile menu
function hideMenu() {
    if (navMenu) {
        navMenu.classList.remove('show-menu');
        document.body.classList.remove('nav-open');
        document.body.style.overflow = '';
        
        // Hide overlay
        if (navOverlay) {
            navOverlay.classList.remove('active');
        }

        // Return focus to toggle button
        if (navToggle) navToggle.focus();
        
        // Track menu close event
        trackEvent('navigation', 'menu_close');
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
        document.body.classList.add('modal-open');

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
            // Allow same-day checkout (for hourly stays)
            checkoutInput.min = today;
            if (!checkoutInput.value) {
                // Default to tomorrow for convenience, but user can select today
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                checkoutInput.value = tomorrow.toISOString().split('T')[0];
            }
        }

        // Auto-update room options if dates are already set
        setTimeout(() => {
            const checkinValue = checkinInput ? checkinInput.value : null;
            const checkoutValue = checkoutInput ? checkoutInput.value : null;

            if (checkinValue && checkoutValue) {
                // Force room options update
                updateRoomOptions();
            }

            // Focus first input for accessibility
            const firstInput = reservationModal.querySelector('input, select');
            if (firstInput) firstInput.focus();
        }, 200);

        // Track modal opening
        trackEvent('reservation', 'modal_open', packageType);
    }
}

// Close reservation modal - ENHANCED VERSION
function closeReservationModal() {
    if (reservationModal) {
        // Remove all possible modal classes
        reservationModal.classList.remove('show', 'show-modal');

        // Force hide with inline styles
        reservationModal.style.display = 'none';
        reservationModal.style.opacity = '0';
        reservationModal.style.visibility = 'hidden';

        // Reset body styles
        document.body.style.overflow = '';
        document.body.classList.remove('modal-open');

        // DON'T close SweetAlert2 here - let it show above the modal
        // if (typeof Swal !== 'undefined') {
        //     Swal.close();
        // }

        // Limpiar cualquier notificación existente (but not SweetAlert2)
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        // Return focus to trigger element
        const triggerBtn = document.querySelector('.floating-cta__btn, .nav__cta');
        if (triggerBtn) triggerBtn.focus();

        // Track modal closing
        trackEvent('reservation', 'modal_close');

        console.log('✅ Modal closed successfully');
    }
}

// Handle reservation form submission with availability check
function handleReservationSubmit(e, form = null) {
    // Support both direct form event and delegated calls
    const targetForm = form || e.target;
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

// API Configuration handled by config.js

// Check room availability - COMMENTED OUT - Using checkAvailability from config.js
/*
async function checkAvailability(params) {
    console.log('checkAvailability called with params:', params);
    try {
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
            }
        });
        
        console.log('Response:', response);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
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
        console.error('Error:', error);
        return {
            success: false,
            error: error.message
        };
    }
}
*/

// Submit reservation to backend - COMMENTED OUT - Using createReservation from config.js
/*
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
*/

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

        // Extract available rooms from the nested response structure
        let availableRooms = [];
        if (availabilityResponse.success && availabilityResponse.data) {
            // Handle nested data structure: response.data.data or response.data directly
            const roomsData = availabilityResponse.data.data || availabilityResponse.data;
            availableRooms = roomsData.habitaciones_disponibles || [];
        }

        console.log('Available rooms for reservation:', availableRooms);

        if (!availabilityResponse.success || availableRooms.length === 0) {
            throw new Error('No hay habitaciones disponibles para las fechas seleccionadas. Por favor, elige otras fechas.');
        }

        // Find suitable room from available rooms
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

        // Use createReservation from config.js instead of submitReservation
        const response = await createReservation(reservationPayload);

        if (response.success) {
            // Primero cerrar el modal INMEDIATAMENTE
            closeReservationModal();

            // Forzar cierre del modal con estilos directos
            const modal = document.getElementById('reservation-modal');
            if (modal) {
                modal.classList.remove('show', 'show-modal');
                modal.style.display = 'none';
                modal.style.opacity = '0';
                modal.style.visibility = 'hidden';
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';

            // Limpiar el formulario
            form.reset();

            // Esperar un momento antes de mostrar SweetAlert2 para asegurar que el modal se cerró
            setTimeout(() => {
            // Luego mostrar el mensaje de éxito usando SweetAlert2 con detalles mejorados
            if (response.fallback) {
                showSuccessMessage(response.message || '¡Reserva enviada por WhatsApp! Te contactaremos pronto.');
            } else {
                // Usar la función mejorada de SweetAlert2 para reservas exitosas
                if (typeof window.showReservationSuccess === 'function') {
                    const nights = calculateNights(reservationData.checkin, reservationData.checkout);
                    const total = selectedRoom.precio * nights;

                    window.showReservationSuccess({
                        roomName: selectedRoom.categoria?.nombre || 'Habitación',
                        roomNumber: selectedRoom.numero,
                        checkIn: formatDate(reservationData.checkin),
                        checkOut: formatDate(reservationData.checkout),
                        nights: nights,
                        total: total
                    });
                } else {
                    // Fallback a mensaje simple
                    const message = `¡Reserva creada exitosamente!

Detalles:
• Habitación: ${selectedRoom.numero} (${selectedRoom.categoria?.nombre || 'N/A'})
• Precio por noche: ${formatPrice(selectedRoom.precio)}
• Total estimado: ${formatPrice(selectedRoom.precio * calculateNights(reservationData.checkin, reservationData.checkout))}

Te contactaremos pronto para confirmar.`;
                    showSuccessMessage(message);
                }
            }

            trackEvent('reservation', response.fallback ? 'submit_fallback' : 'submit_success');
            }, 100); // Wait 100ms to ensure modal is closed
        } else {
            throw new Error(response.message || 'Error al procesar la reserva');
        }

    } catch (error) {
        console.error('Error in availability check or reservation:', error);

        // If it's an availability issue, offer WhatsApp alternative
        if (error.message.includes('disponibles') || error.message.includes('alternativas')) {
            const message = createWhatsAppMessage(reservationData);
            const whatsappUrl = generateWhatsAppURL(message, 'availability_fallback');

            // Cerrar modal primero
            closeReservationModal();
            form.reset();

            // Abrir WhatsApp
            window.open(whatsappUrl, '_blank');

            // Mostrar mensaje explicativo
            showErrorMessage(error.message + '\n\nTe hemos redirigido a WhatsApp para ayudarte con alternativas.');
        } else {
            // Para otros errores, mantener el modal abierto para que el usuario pueda corregir
            showErrorMessage(error.message || 'Error al verificar disponibilidad. Por favor, intenta nuevamente.');
        }

        trackEvent('reservation', 'availability_error', error.message);
    } finally {
        // Restore form state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        form.querySelectorAll('input, select, textarea').forEach(field => field.disabled = false);

        // Cerrar loading de SweetAlert2 si está abierto
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            Swal.close();
        }
    }
}

// Find suitable room from available rooms
function findSuitableRoom(availableRooms, selectedRoomId, guests) {
    if (!availableRooms || availableRooms.length === 0) {
        return null;
    }

    const guestCount = parseInt(guests);

    // If a specific room ID was selected, find that room
    if (selectedRoomId && selectedRoomId !== '') {
        const selectedRoom = availableRooms.find(room => room.id == selectedRoomId);
        if (selectedRoom && selectedRoom.capacidad >= guestCount) {
            return selectedRoom;
        }
    }

    // Otherwise, find any room with sufficient capacity
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
    const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    // Si es 0 noches (mismo día), cobrar como 1 día completo
    return nights === 0 ? 1 : nights;
}

// Format price for display (simple version, will use config.js version when available)
function formatPrice(amount) {
    // Check if there's a formatPrice function from config.js that's different from this one
    if (typeof window.formatPriceConfig === 'function') {
        return window.formatPriceConfig(amount);
    }
    
    // Get currency symbol from global variable (set when receiving availability data)
    const currencySymbol = window.hotelCurrencySymbol || '$';
    
    // Format the number with thousands separator
    const formattedNumber = new Intl.NumberFormat('es-GT', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount);
    
    // Return with currency symbol
    return `${currencySymbol} ${formattedNumber}`;
}

// Update room options based on availability
async function updateRoomOptions() {
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const guestsInput = document.getElementById('guests');
    const roomSelect = document.getElementById('room-type');

    if (!checkinInput.value || !checkoutInput.value || !roomSelect) {
        console.log('Missing required inputs for availability check');
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

        console.log('Checking availability with params:', availabilityParams);
        
        // Check if checkAvailability function is available
        if (typeof checkAvailability !== 'function') {
            throw new Error('checkAvailability function not available. Make sure config.js is loaded first.');
        }
        
        const response = await checkAvailability(availabilityParams);
        console.log('Full availability response:', response);
        
        // The response structure is: {success: true, data: {data: {habitaciones_disponibles: [...]}}}
        // We need to check the nested data structure
        const roomsData = response.data?.data || response.data;
        console.log('Rooms data extracted:', roomsData);
        
        // Store currency symbol globally from the response
        if (roomsData.moneda) {
            window.hotelCurrencySymbol = roomsData.moneda;
            console.log('Currency symbol set to:', window.hotelCurrencySymbol);
        }

        if (response.success && roomsData && roomsData.habitaciones_disponibles && roomsData.habitaciones_disponibles.length > 0) {
            console.log('Found', roomsData.habitaciones_disponibles.length, 'available rooms');
            updateRoomSelectOptions(roomsData.habitaciones_disponibles, availabilityParams);
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

    // Build options HTML showing individual rooms
    let optionsHTML = '<option value="">Seleccionar habitación disponible</option>';

    // Sort rooms by numero for better organization
    availableRooms.sort((a, b) => {
        const numA = parseInt(a.numero) || 0;
        const numB = parseInt(b.numero) || 0;
        return numA - numB;
    });

    // Add each individual room as an option
    availableRooms.forEach(room => {
        const totalPrice = room.precio * nights;
        const categoryName = room.categoria?.nombre || 'Estándar';
        const priceText = `${formatPrice(room.precio)}/noche - Total: ${formatPrice(totalPrice)}`;
        
        // Store room ID as value and additional data as attributes
        optionsHTML += `<option value="${room.id}" 
            data-room-number="${room.numero}" 
            data-price="${room.precio}" 
            data-capacity="${room.capacidad || 2}"
            data-category="${categoryName}">
            Habitación ${room.numero} - ${categoryName} - ${priceText}
        </option>`;
    });

    roomSelect.innerHTML = optionsHTML;

    // Auto-trigger price summary update if only one room is available
    if (availableRooms.length === 1) {
        // Pre-select the only available room
        roomSelect.selectedIndex = 1; // Skip the "Seleccionar" option
        updatePriceSummary();
    }

    // Show success message with availability info
    console.log(`Se encontraron ${availableRooms.length} habitaciones disponibles para las fechas seleccionadas.`);
    // Remove SweetAlert success message to avoid modal conflicts
}

// Show warning message
function showWarningMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Atención',
            text: message,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            zIndex: 10000
        });
    } else {
        showNotification(message, 'warning');
    }
}

// Show info message
function showInfoMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Información',
            text: message,
            icon: 'info',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            zIndex: 10000
        });
    } else {
        showNotification(message, 'info');
    }
}

// ===== CLIENT SEARCH FUNCTIONS (Phase 3) =====

// Search for existing clients by email or phone
async function searchExistingClient(searchTerm) {
    if (!searchTerm || searchTerm.length < 3) {
        return { success: false, data: null };
    }

    try {
        const response = await searchClient(searchTerm);

        // Check if API returned success and has cliente data
        if (response.success && response.data && response.data.cliente) {
            return {
                success: true,
                data: response.data.cliente
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
        // Apply phone mask when autofilling
        const formattedPhone = applyPhoneMask(clientData.telefono);
        phoneInput.value = formattedPhone;
        phoneInput.classList.add('auto-filled');
    }

    // Show success message - TEMPORARILY DISABLED FOR DEBUG
    console.log('🚨 autoFillClientData ejecutado para cliente:', clientData.nombre);
    // showSuccessMessage('¡Cliente encontrado! Datos completados automáticamente.');
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

// Show searching indicator
function showSearchingIndicator(inputElement, searchTerm) {
    const form = document.getElementById('reservation-form');
    if (!form) return;
    
    const searchType = searchTerm.includes('@') ? 'correo' : 
                      (searchTerm.includes(' ') ? 'nombre' : 'teléfono');
    
    const indicatorHTML = `
        <div class="client-search-indicator">
            <div class="search-indicator__content">
                <div class="search-indicator__spinner"></div>
                <span class="search-indicator__text">
                    <i class="ri-search-line"></i>
                    Buscando cliente por ${searchType}...
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = indicatorHTML;
    if (formActions) {
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        form.appendChild(indicatorDiv.firstElementChild);
    }
}

// Remove searching indicator
function removeSearchingIndicator() {
    const indicator = document.querySelector('.client-search-indicator');
    if (indicator) {
        indicator.remove();
    }
}

// Show client not found message
function showClientNotFound(searchTerm) {
    const form = document.getElementById('reservation-form');
    if (!form) return;
    
    const searchType = searchTerm.includes('@') ? 'correo' : 
                      (searchTerm.includes(' ') ? 'nombre' : 'teléfono');
    
    const notFoundHTML = `
        <div class="client-search-indicator client-search-indicator--not-found">
            <div class="search-indicator__content">
                <span class="search-indicator__text">
                    <i class="ri-information-line"></i>
                    Cliente no encontrado por ${searchType}. Será registrado como nuevo cliente.
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = notFoundHTML;
    if (formActions) {
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        form.appendChild(indicatorDiv.firstElementChild);
    }
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const indicator = document.querySelector('.client-search-indicator--not-found');
        if (indicator) {
            indicator.remove();
        }
    }, 3000);
}

// Show client lookup error
function showClientLookupError() {
    const form = document.getElementById('reservation-form');
    if (!form) return;
    
    const errorHTML = `
        <div class="client-search-indicator client-search-indicator--error">
            <div class="search-indicator__content">
                <span class="search-indicator__text">
                    <i class="ri-error-warning-line"></i>
                    Error al buscar cliente. Por favor, continúa con el registro.
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = errorHTML;
    if (formActions) {
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        form.appendChild(indicatorDiv.firstElementChild);
    }
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const indicator = document.querySelector('.client-search-indicator--error');
        if (indicator) {
            indicator.remove();
        }
    }, 3000);
}

// ===== NEW UX INDICATOR FUNCTIONS =====

// Show typing indicator
function showTypingIndicator() {
    console.log('🚀 Ejecutando showTypingIndicator');
    const form = document.getElementById('reservation-form');
    if (!form) {
        console.error('❌ Form no encontrado');
        return;
    }
    
    const indicatorHTML = `
        <div class="client-search-indicator client-search-indicator--typing">
            <div class="search-indicator__content">
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
                <span class="search-indicator__text">
                    <i class="ri-edit-2-line"></i>
                    Escribiendo nombre... Escribe tu nombre completo para buscar si ya eres cliente.
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    console.log('📍 FormActions encontrado:', formActions);
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = indicatorHTML;
    if (formActions) {
        console.log('✅ Insertando indicador antes de formActions');
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        console.log('⚠️ FormActions no encontrado, agregando al final');
        form.appendChild(indicatorDiv.firstElementChild);
    }
}

// Show client found message
function showClientFoundMessage(clientName) {
    const form = document.getElementById('reservation-form');
    if (!form) return;
    
    const foundHTML = `
        <div class="client-search-indicator client-search-indicator--found">
            <div class="search-indicator__content">
                <span class="search-indicator__text">
                    <i class="ri-check-double-line"></i>
                    ¡Cliente encontrado! <strong>${clientName}</strong>. Datos completados automáticamente. Puedes proceder con la reserva.
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = foundHTML;
    if (formActions) {
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        form.appendChild(indicatorDiv.firstElementChild);
    }
}

// Show new client message
function showNewClientMessage() {
    const form = document.getElementById('reservation-form');
    if (!form) return;
    
    const newClientHTML = `
        <div class="client-search-indicator client-search-indicator--new-client">
            <div class="search-indicator__content">
                <span class="search-indicator__text">
                    <i class="ri-user-add-line"></i>
                    Cliente nuevo. Por favor, completa los datos de contacto para continuar.
                </span>
            </div>
        </div>
    `;
    
    const formActions = form.querySelector('.form__actions');
    const indicatorDiv = document.createElement('div');
    indicatorDiv.innerHTML = newClientHTML;
    if (formActions) {
        form.insertBefore(indicatorDiv.firstElementChild, formActions);
    } else {
        form.appendChild(indicatorDiv.firstElementChild);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        const indicator = document.querySelector('.client-search-indicator--new-client');
        if (indicator) {
            indicator.remove();
        }
    }, 5000);
}

// Remove all search indicators
function removeAllSearchIndicators() {
    const indicators = document.querySelectorAll('.client-search-indicator');
    indicators.forEach(indicator => indicator.remove());
}

// Enhanced client lookup with improved UX feedback
async function handleClientLookup(inputElement, searchTerm) {
    console.log('🚀 handleClientLookup llamado con searchTerm:', JSON.stringify(searchTerm));
    console.log('📍 Elemento que lo llamó:', inputElement.id);
    // Clear existing auto-fill styling
    const formInputs = document.querySelectorAll('#reservation-form input');
    formInputs.forEach(input => input.classList.remove('auto-filled'));

    // Remove existing client history and search indicators
    const existingHistory = document.querySelector('.client-history');
    if (existingHistory) {
        existingHistory.remove();
    }
    
    const existingSearchIndicator = document.querySelector('.client-search-indicator');
    if (existingSearchIndicator) {
        existingSearchIndicator.remove();
    }

    // Validate search term
    if (!searchTerm || 
        (searchTerm.includes('@') && searchTerm.length < 5) || 
        (!searchTerm.includes('@') && !searchTerm.includes(' ') && searchTerm.replace(/\D/g, '').length < 7) ||
        (searchTerm.includes(' ') && searchTerm.length < 5)) {
        return;
    }

    // Show immediate visual feedback
    showSearchingIndicator(inputElement, searchTerm);
    
    // Add loading indicator to input
    inputElement.classList.add('input-loading');

    try {
        const result = await searchExistingClient(searchTerm);

        // Remove searching indicator
        removeSearchingIndicator();

        if (result.success && result.data) {
            autoFillClientData(result.data);
            showClientHistory(result.data, result.history);

            // Track successful client lookup
            const searchType = searchTerm.includes('@') ? 'email' : 
                              (searchTerm.includes(' ') ? 'name' : 'phone');
            trackEvent('client', 'found', searchType);
        } else {
            // Show "not found" message for a brief moment
            showClientNotFound(searchTerm);
        }

    } catch (error) {
        console.error('Client lookup error:', error);
        removeSearchingIndicator();
        showClientLookupError();
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
    
    // Mostrar texto apropiado según si es estadía del mismo día o múltiples noches
    const nightsText = nights === 1 && checkinInput.value === checkoutInput.value 
        ? '1 día (mismo día)' 
        : `${nights} noche${nights !== 1 ? 's' : ''}`;
    document.getElementById('price-nights').textContent = nightsText;
    
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
    
    // NO USAR TRIM PARA PRESERVAR ESPACIOS
    // Solo escapar caracteres peligrosos HTML, pero permitir espacios
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#x27;')
        .replace(/\//g, '&#x2F;');
    // NO .trim() aquí - preservar espacios en solicitudes especiales
}

// Sanitize email input
function sanitizeEmail(email) {
    if (typeof email !== 'string') return '';
    return email.toLowerCase().trim().replace(/[^\w@.-]/g, '');
}

// Sanitize phone input - now supports the new phone mask format
function sanitizePhone(phone) {
    if (typeof phone !== 'string') return '';
    return phone.replace(/[^\d\s\-\+\(\)]/g, '').trim();
}

// Function to get only numeric digits from phone (for database storage)
function getNumericPhone(phone) {
    if (typeof phone !== 'string') return '';
    return phone.replace(/\D/g, '');
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
                // Keep formatted phone for display, but also store numeric version for validation
                sanitized[key] = sanitizePhone(value);
                sanitized[key + '_numeric'] = getNumericPhone(value);
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
    
    // Normalizar fechas a medianoche para comparación correcta
    checkinDate.setHours(0, 0, 0, 0);
    checkoutDate.setHours(0, 0, 0, 0);
    
    // DEBUG: Log de fechas para verificar normalización
    console.log('=== VALIDACIÓN DE FECHAS ===');
    console.log('Fecha checkin (input):', data.checkin);
    console.log('Fecha checkout (input):', data.checkout);
    console.log('Fecha checkin (Date normalizado):', checkinDate.toISOString());
    console.log('Fecha checkout (Date normalizado):', checkoutDate.toISOString());
    console.log('Fecha today (Date normalizado):', today.toISOString());
    console.log('checkinDate < today?', checkinDate < today);
    console.log('checkinDate.getTime():', checkinDate.getTime());
    console.log('today.getTime():', today.getTime());
    console.log('========================');

    // Permitir reservas desde hoy (>=, no solo >)
    if (checkinDate < today) {
        console.error('❌ ERROR: La fecha de llegada es anterior a hoy');
        errors.push('La fecha de llegada no puede ser anterior a hoy');
    } else {
        console.log('✅ OK: La fecha de llegada es válida');
    }

    // Allow same-day stays (check-in and check-out on the same day)
    // Only validate that checkout is not BEFORE checkin
    if (checkoutDate < checkinDate) {
        errors.push('La fecha de salida no puede ser anterior a la fecha de llegada');
    }

    // Enhanced email validation
    if (data.guestEmail && !validateEmail(data.guestEmail)) {
        errors.push('El formato del correo electrónico no es válido');
    }

    // Enhanced phone validation - check numeric version for length
    if (data.guestPhone) {
        const numericPhone = getNumericPhone(data.guestPhone);
        if (numericPhone.length < 7 || numericPhone.length > 15) {
            errors.push('El número de teléfono debe tener entre 7 y 15 dígitos');
        }
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
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            heightAuto: false
        });
    } else {
        showNotification(message, 'success');
    }
}

// Show error message
function showErrorMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            heightAuto: false
        });
    } else {
        showNotification(message, 'error');
    }
}

// Show notification with improved z-index for modal compatibility
function showNotification(message, type = 'info') {
    // Si SweetAlert2 está disponible, usarlo directamente
    if (typeof Swal !== 'undefined') {
        const swalIcon = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        }[type] || 'info';

        Swal.fire({
            title: type === 'error' ? 'Error' : type === 'warning' ? 'Atención' : 'Información',
            text: message,
            icon: swalIcon,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            heightAuto: false
        });
        return;
    }

    // Fallback a notificaciones custom con z-index corregido
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

    // Add styles with higher z-index than modal
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 2500;
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
function initializeLandingPage() {
    // Navigation event listeners
    if (navToggle) {
        navToggle.addEventListener('click', showMenu);
    }

    if (navClose) {
        navClose.addEventListener('click', hideMenu);
    }

    // Overlay click to close menu
    if (navOverlay) {
        navOverlay.addEventListener('click', hideMenu);
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
    const nameInput = document.getElementById('guest-name');

    // Debounced functions
    const debouncedUpdateRooms = debounce(updateRoomOptions, 1000);
    const debouncedUpdatePrice = debounce(updatePriceSummary, 500);
    const debouncedClientLookup = debounce(handleClientLookup, 800);
    
    // Initialize improved client search UX
    initializeClientSearchUX();

    // Client lookup event listeners - TEMPORARILY DISABLED FOR DEBUG
    console.log('⚠️ Email y Phone listeners desactivados temporalmente');
    // if (emailInput) {
    //     emailInput.addEventListener('input', (e) => {
    //         const email = e.target.value.trim();
    //         if (email.includes('@') && email.length > 5) {
    //             debouncedClientLookup(e.target, email);
    //         }
    //     });
    // }

    // if (phoneInput) {
    //     phoneInput.addEventListener('input', (e) => {
    //         const phone = e.target.value.trim();
    //         if (phone.length > 7) {
    //             debouncedClientLookup(e.target, phone);
    //         }
    //     });
    // }

    // Note: Client search for name is now handled by initializeClientSearchUX()

    if (checkinInput) {
        checkinInput.addEventListener('change', (e) => {
            if (checkoutInput) {
                const checkinDate = new Date(e.target.value);
                // Allow same-day checkout (minimum is same day as checkin)
                checkoutInput.min = e.target.value;

                // Only update checkout if it's BEFORE checkin (invalid)
                if (checkoutInput.value && new Date(checkoutInput.value) < checkinDate) {
                    // Set to same day as checkin (user can adjust if needed)
                    checkoutInput.value = e.target.value;
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
window.handleReservationSubmit = handleReservationSubmit;

// ===== GLOBAL FUNCTION EXPORTS =====
// Expose critical functions to global scope to prevent call issues
window.updateRoomOptions = updateRoomOptions;
window.updatePriceSummary = updatePriceSummary;
window.openReservationModal = openReservationModal;
window.closeReservationModal = closeReservationModal;
window.showSuccessMessage = showSuccessMessage;
window.showErrorMessage = showErrorMessage;
window.showWarningMessage = showWarningMessage;
window.showInfoMessage = showInfoMessage;

// CRITICAL FIX: Failsafe modal closer
window.forceCloseModal = function() {
    const modal = document.getElementById('reservation-modal');
    if (modal) {
        modal.classList.remove('show', 'show-modal');
        modal.style.cssText = 'display: none !important; opacity: 0 !important; visibility: hidden !important; z-index: -1 !important;';
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        console.log('⚠️ Failsafe modal close activated');
    }
};

// Debug function to test modal functionality
window.testModalFunctions = function() {
    console.log('=== TESTING MODAL FUNCTIONS ===');
    console.log('updateRoomOptions:', typeof window.updateRoomOptions);
    console.log('updatePriceSummary:', typeof window.updatePriceSummary);
    console.log('openReservationModal:', typeof window.openReservationModal);
    console.log('closeReservationModal:', typeof window.closeReservationModal);
    console.log('Swal available:', typeof Swal !== 'undefined');

    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const roomSelect = document.getElementById('room-type');
    const priceSummary = document.getElementById('price-summary');

    console.log('DOM Elements:');
    console.log('- checkin input:', !!checkinInput);
    console.log('- checkout input:', !!checkoutInput);
    console.log('- room select:', !!roomSelect);
    console.log('- price summary:', !!priceSummary);

    if (checkinInput && checkoutInput) {
        console.log('- checkin value:', checkinInput.value);
        console.log('- checkout value:', checkoutInput.value);
    }

    return 'All functions tested. Check console for details.';
};

// Initialization is now handled by initializeWhenReady() function at the end of the file

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

// ===== FONT LOADING FALLBACK =====
// Detect font loading failures and activate fallback
function initFontFallback() {
    // Set timeout for font loading detection
    const fontLoadTimeout = 3000; // 3 seconds

    setTimeout(() => {
        try {
            // Try to detect if custom fonts loaded by measuring text width
            const testElement = document.createElement('div');
            testElement.style.fontFamily = '"Playfair Display", serif';
            testElement.style.fontSize = '72px';
            testElement.style.position = 'absolute';
            testElement.style.left = '-9999px';
            testElement.textContent = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            document.body.appendChild(testElement);

            const customFontWidth = testElement.offsetWidth;

            testElement.style.fontFamily = 'serif';
            const fallbackFontWidth = testElement.offsetWidth;

            // If widths are the same, custom font likely didn't load
            if (Math.abs(customFontWidth - fallbackFontWidth) < 5) {
                console.warn('Custom fonts may not have loaded, activating fallback');
                document.documentElement.classList.add('font-fallback-active');
            }

            document.body.removeChild(testElement);
        } catch (error) {
            console.warn('Font detection failed, activating fallback:', error);
            document.documentElement.classList.add('font-fallback-active');
        }
    }, fontLoadTimeout);
}

// Initialize font fallback detection
document.addEventListener('DOMContentLoaded', initFontFallback);

// ===== IMPROVED CLIENT SEARCH UX SYSTEM =====

// Initialize the improved UX flow for client search - SIMPLE APPROACH
let clientSearchUXInitialized = false;
function initializeClientSearchUX() {
    if (clientSearchUXInitialized) {
        console.log('⚠️ initializeClientSearchUX ya fue inicializado, saltando...');
        return;
    }
    clientSearchUXInitialized = true;
    console.log('🚀 Inicializando ClientSearchUX por primera vez...');
    const nameInput = document.getElementById('guest-name');
    const emailInput = document.getElementById('guest-email');
    const phoneInput = document.getElementById('guest-phone');
    const specialRequestsInput = document.getElementById('special-requests');
    
    // Initially disable all fields except name
    setFieldsDisabledState(true, true, false); // (disabled, name enabled, submit disabled)
    
    if (nameInput) {
        let isSearching = false;
        
        // CAPTURE PHASE: Intercept space key BEFORE anything else
        nameInput.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.keyCode === 32) {
                console.log('🚀 CAPTURE PHASE: Espacio detectado!');
                // Stop propagation to prevent other listeners from blocking
                e.stopImmediatePropagation();
                // Let the space through
                return true;
            }
        }, true); // true = use capture phase
        
        // NO INPUT VALIDATION - let user type freely
        nameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            let previousValue = e.target.getAttribute('data-previous-value') || '';
            
            console.log('📝 Input event - Anterior:', JSON.stringify(previousValue));
            console.log('📝 Input event - Actual:', JSON.stringify(value));
            
            // Detect if space was added
            if (value.includes(' ') && !previousValue.includes(' ')) {
                console.log('✨ ¡ESPACIO AGREGADO EXITOSAMENTE!');
            }
            
            // Store current value for next comparison
            e.target.setAttribute('data-previous-value', value);
            
            // NO MODIFICATION OF THE VALUE - let user type anything
        });
        
        // Main validation: when user tries to move to next field
        nameInput.addEventListener('blur', async function(e) {
            const name = e.target.value.trim();
            console.log('🔍 Usuario salió del campo nombre. Validando:', name);
            
            // Only search if we have a reasonable name
            if (name.length >= 3) {
                if (!isSearching) {
                    isSearching = true;
                    await validateClientName(name);
                    isSearching = false;
                }
            } else {
                // Name too short, enable fields for new client
                console.log('ℹ️ Nombre muy corto, habilitando campos para nuevo cliente');
                enableFieldsForNewClient();
            }
        });
        
        // FORCE SPACE TO WORK - Use capture phase and ensure space always passes
        nameInput.addEventListener('keydown', function(e) {
            console.log('🎹 Tecla presionada:', e.key, 'KeyCode:', e.keyCode);
            
            if (e.key === ' ' || e.keyCode === 32) {
                console.log('🗺️ ¡BARRA ESPACIADORA PRESIONADA!');
                console.log('🔍 Valor actual antes del espacio:', JSON.stringify(e.target.value));
                
                // Force the space to be added if it's being blocked
                e.stopPropagation();
                e.stopImmediatePropagation();
                // DO NOT preventDefault - let the space through
                return true;
            }
            
            if (e.key === 'Tab') {
                // Let the blur event handle the validation
                console.log('📝 Usuario presionó Tab, blur se ejecutará automáticamente');
            }
            
            if (e.key === 'Enter') {
                e.preventDefault();
                // Trigger validation and move to email field
                const name = e.target.value.trim();
                if (name.length >= 3) {
                    validateClientName(name).then(() => {
                        const emailField = document.getElementById('guest-email');
                        if (emailField && !emailField.disabled) {
                            emailField.focus();
                        }
                    });
                }
            }
        });
        
        // DEBUG: Also monitor keyup events + FORCE SPACE if missing
        nameInput.addEventListener('keyup', function(e) {
            if (e.key === ' ' || e.keyCode === 32) {
                console.log('🎆 KEYUP de espacio - Valor actual:', JSON.stringify(e.target.value));
                
                // FALLBACK: If space wasn't added by normal means, add it manually
                const cursorPos = e.target.selectionStart;
                const textBefore = e.target.value.substring(0, cursorPos);
                const textAfter = e.target.value.substring(cursorPos);
                
                // Check if there's no space at cursor position
                if (cursorPos > 0 && textBefore.charAt(cursorPos - 1) !== ' ') {
                    console.log('⚠️ ESPACIO PERDIDO! Agregando manualmente...');
                    const newValue = textBefore + ' ' + textAfter;
                    e.target.value = newValue;
                    // Restore cursor position after the space
                    e.target.setSelectionRange(cursorPos + 1, cursorPos + 1);
                    console.log('✅ Espacio agregado manualmente. Nuevo valor:', JSON.stringify(e.target.value));
                }
            }
        });
    }
    
    // Initialize phone mask with Guatemala code (502) pre-populated and editable
    if (phoneInput) {
        // Pre-populate with Guatemala code if empty
        if (!phoneInput.value || phoneInput.value.trim() === '') {
            phoneInput.value = '(502) ';
            phoneInput.setAttribute('data-default-code', '502');
        }
        
        phoneInput.addEventListener('focus', function(e) {
            // If field is empty or has placeholder, set default code
            if (!e.target.value || e.target.value.trim() === '') {
                e.target.value = '(502) ';
            }
            // Move cursor to end (after the code, ready for 8-digit number)
            setTimeout(() => {
                e.target.setSelectionRange(e.target.value.length, e.target.value.length);
            }, 0);
        });
        
        phoneInput.addEventListener('input', function(e) {
            applyPhoneMask(e.target);
            console.log('📞 Teléfono formateado:', e.target.value);
        });
        
        phoneInput.addEventListener('keydown', function(e) {
            const cursorPos = e.target.selectionStart;
            const value = e.target.value;
            
            // Allow: backspace, delete, tab, escape, enter
            if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                
                // Prevent deleting below minimum format "(XXX) "
                if (e.keyCode === 8 && cursorPos <= 6) {
                    e.preventDefault();
                    return;
                }
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }
}

// NEW SIMPLE APPROACH: Validate client name when user moves to next field
async function validateClientName(name) {
    console.log('🔍 Validando cliente:', name);
    
    // Clean up name (remove extra spaces)
    const cleanName = name.replace(/\s+/g, ' ').trim();
    
    try {
        const result = await searchExistingClient(cleanName);
        
        if (result.success && result.data) {
            // Client found! Show welcome message and auto-fill
            console.log('🎉 Cliente encontrado:', result.data.nombre);

            // Auto-fill email and phone only
            autoFillClientDataSilent(result.data);

            // Show welcome SweetAlert
            showWelcomeMessage(result.data.nombre);

            // Enable all fields including submit button (allow client to edit and confirm)
            setFieldsDisabledState(false, true, true); // all fields enabled, name enabled, submit enabled

            // Track successful client lookup
            trackEvent('client', 'found', 'name');

        } else {
            // Client not found - enable all fields for new client registration
            console.log('👤 Cliente no encontrado, habilitando campos para nuevo cliente');
            enableFieldsForNewClient();
        }
        
    } catch (error) {
        console.error('Error al validar cliente:', error);
        // On error, enable fields so user can continue
        enableFieldsForNewClient();
    }
}

// Enable all fields for new client registration
function enableFieldsForNewClient() {
    setFieldsDisabledState(false, true, true); // Enable all fields
    removeAllSearchIndicators();
    
    // Optional: Show subtle message that it's a new client
    console.log('✨ Habilitando campos para nuevo cliente');
}

// Auto-fill client data without showing success message
function autoFillClientDataSilent(clientData) {
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
        // Apply phone mask when autofilling
        phoneInput.value = clientData.telefono;
        applyPhoneMask(phoneInput);
        phoneInput.classList.add('auto-filled');
    }

    console.log('✨ Datos auto-completados para:', clientData.nombre);
}

// Show welcome message with SweetAlert
function showWelcomeMessage(clientName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Bienvenido!',
            text: `Hola ${clientName}, continúa con tu reserva!`,
            icon: 'success',
            confirmButtonText: 'Continuar',
            customClass: {
                popup: 'swal-modal-overlay'
            },
            zIndex: 10000
        });
    } else {
        showNotification(`Bienvenido ${clientName}! Continúa con tu reserva.`, 'success');
    }
}

// Set fields disabled/enabled state
function setFieldsDisabledState(disabled, nameEnabled = true, submitEnabled = false) {
    const emailInput = document.getElementById('guest-email');
    const phoneInput = document.getElementById('guest-phone');
    const specialRequestsInput = document.getElementById('special-requests');
    const submitButton = document.querySelector('#reservation-form .btn--primary');
    const nameInput = document.getElementById('guest-name');
    
    // Email, phone, and special requests
    if (emailInput) {
        emailInput.disabled = disabled;
        emailInput.style.opacity = disabled ? '0.5' : '1';
        emailInput.style.cursor = disabled ? 'not-allowed' : 'text';
    }
    if (phoneInput) {
        phoneInput.disabled = disabled;
        phoneInput.style.opacity = disabled ? '0.5' : '1';
        phoneInput.style.cursor = disabled ? 'not-allowed' : 'text';
    }
    if (specialRequestsInput) {
        specialRequestsInput.disabled = disabled;
        specialRequestsInput.style.opacity = disabled ? '0.5' : '1';
        specialRequestsInput.style.cursor = disabled ? 'not-allowed' : 'text';
    }
    
    // Name field
    if (nameInput) {
        nameInput.disabled = !nameEnabled;
        nameInput.style.opacity = nameEnabled ? '1' : '0.5';
        nameInput.style.cursor = nameEnabled ? 'text' : 'not-allowed';
    }
    
    // Submit button
    if (submitButton) {
        submitButton.disabled = !submitEnabled && disabled;
        submitButton.style.opacity = (!submitEnabled && disabled) ? '0.5' : '1';
        submitButton.style.cursor = (!submitEnabled && disabled) ? 'not-allowed' : 'pointer';
    }
}

// Apply phone mask with Guatemala code (502) pre-populated
// Format: (502) 5544-5566 or (XXX) XXXX-XXXX for international
function applyPhoneMask(input) {
    let value = input.value.replace(/\D/g, ''); // Remove all non-digits
    let formattedValue = '';
    
    if (value.length > 0) {
        // Format: (XXX) XXXX-XXXX
        // First 3 digits = area/country code (502)
        // Next 4 digits = first part of phone (5544)
        // Last 4 digits = second part of phone (5566)
        // Total: 3 + 8 = 11 digits for Guatemala
        if (value.length <= 3) {
            formattedValue = `(${value}`;
        } else if (value.length <= 7) {
            // (502) 5544
            formattedValue = `(${value.substring(0, 3)}) ${value.substring(3)}`;
        } else {
            // (502) 5544-5566 - Allow up to 15 digits total for international
            formattedValue = `(${value.substring(0, 3)}) ${value.substring(3, 7)}-${value.substring(7, 15)}`;
        }
    } else {
        // If user deletes everything, restore default Guatemala code
        formattedValue = '(502) ';
    }
    
    input.value = formattedValue;
    
    // NO CLIENT LOOKUP - Just format the phone number
}

// Get raw phone number (without formatting)
function getRawPhoneNumber(formattedPhone) {
    return formattedPhone.replace(/[^\d]/g, '');
}

// Validate phone number format
function isValidPhoneNumber(phone) {
    const rawPhone = getRawPhoneNumber(phone);
    return rawPhone.length >= 8 && rawPhone.length <= 11;
}

// ===== INITIALIZATION =====
// Wait for both DOM and all dependencies to be loaded
function initializeWhenReady() {
    // Check if required dependencies are available
    const dependenciesReady = typeof checkAvailability === 'function' && 
                             typeof generateWhatsAppURL === 'function' &&
                             typeof apiRequest === 'function';
    
    if (dependenciesReady) {
        console.log('✓ All dependencies loaded, initializing...');
        // Initialize DOM elements
        initializeDOMElements();
        // Initialize main functionality
        initializeLandingPage();
    } else {
        // Wait a bit more and try again
        setTimeout(initializeWhenReady, 100);
        console.log('⏳ Waiting for dependencies to load...');
    }
}

// Start initialization after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeWhenReady);
} else {
    // DOM already loaded, start immediately
    initializeWhenReady();
}

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
