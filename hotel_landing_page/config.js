// ===== API CONFIGURATION =====
// Configuration for Casa Vieja Hotel API integration

// Auto-detect environment and set appropriate base URL
const detectBaseURL = () => {
    const currentHost = window.location.hostname;
    
    // Production environment
    if (currentHost === 'casaviejahotel.com' || currentHost === 'www.casaviejahotel.com') {
        return 'https://casaviejahotel.com/api';
    }
    
    // Staging environment  
    if (currentHost.includes('staging') || currentHost.includes('dev')) {
        return 'https://staging.casaviejahotel.com/api';
    }
    
    // Local development - check common Laravel ports
    const commonPorts = [8000, 8001, 8080];
    return `http://localhost:8001/api`; // Default Laravel Sail port
};

const API_CONFIG = {
    BASE_URL: detectBaseURL(),
    ENDPOINTS: {
        DISPONIBILIDAD: '/reservas/disponibilidad',
        CREAR_RESERVA: '/reservas',
        BUSCAR_CLIENTE: '/clientes/buscar',
        CALENDARIO: '/reservas/calendario'
    },
    
    // Request timeout in milliseconds (adjusted for different operations)
    TIMEOUT: {
        DEFAULT: 10000,      // 10 seconds for most requests
        AVAILABILITY: 15000, // 15 seconds for availability checks
        RESERVATION: 20000   // 20 seconds for reservation creation
    },
    
    // Retry configuration with exponential backoff
    RETRY: {
        MAX_ATTEMPTS: 3,
        DELAY: 1000, // Base delay in milliseconds
        BACKOFF_FACTOR: 2 // Exponential backoff multiplier
    },
    
    // Headers for API requests
    HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    
    // Debug mode (enable in development)
    DEBUG: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
};

// ===== API UTILITY FUNCTIONS =====

/**
 * Make API request with retry logic and improved error handling
 * @param {string} endpoint - API endpoint
 * @param {object} options - Fetch options
 * @param {number} timeout - Custom timeout (optional)
 * @returns {Promise} - API response
 */
async function apiRequest(endpoint, options = {}, timeout = null) {
    const url = `${API_CONFIG.BASE_URL}${endpoint}`;
    const requestTimeout = timeout || API_CONFIG.TIMEOUT.DEFAULT;
    
    const config = {
        ...options,
        headers: {
            ...API_CONFIG.HEADERS,
            ...options.headers
        }
    };
    
    if (API_CONFIG.DEBUG) {
        console.log(`🔗 API Request: ${options.method || 'GET'} ${url}`, config);
    }
    
    let lastError;
    
    for (let attempt = 1; attempt <= API_CONFIG.RETRY.MAX_ATTEMPTS; attempt++) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), requestTimeout);
            
            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                const errorText = await response.text().catch(() => 'Unknown error');
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const data = await response.json();
            
            if (API_CONFIG.DEBUG) {
                console.log(`✅ API Response: ${url}`, data);
            }
            
            return data;
            
        } catch (error) {
            lastError = error;
            
            if (API_CONFIG.DEBUG) {
                console.warn(`⚠️ API request attempt ${attempt} failed:`, error.message);
            }
            
            // Don't retry on certain errors
            if (error.name === 'AbortError') {
                throw new Error('Connection timeout');
            }
            
            if (error.message.includes('400') || 
                error.message.includes('401') || 
                error.message.includes('403') ||
                error.message.includes('404')) {
                break;
            }
            
            // Wait before retry with exponential backoff
            if (attempt < API_CONFIG.RETRY.MAX_ATTEMPTS) {
                const delay = API_CONFIG.RETRY.DELAY * Math.pow(API_CONFIG.RETRY.BACKOFF_FACTOR, attempt - 1);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    
    throw lastError;
}

/**
 * Check room availability
 * @param {object} params - Availability parameters
 * @returns {Promise} - Availability response
 */
async function checkAvailability(params) {
    try {
        const queryParams = new URLSearchParams({
            fecha_entrada: params.checkin,
            fecha_salida: params.checkout,
            numero_huespedes: params.guests || 2,
            categoria_id: params.categoryId || '',
            nivel_id: params.levelId || ''
        });
        
        const response = await apiRequest(
            `${API_CONFIG.ENDPOINTS.DISPONIBILIDAD}?${queryParams}`,
            { method: 'GET' },
            API_CONFIG.TIMEOUT.AVAILABILITY
        );
        
        return response;
        
    } catch (error) {
        console.error('Error checking availability:', error);
        throw error;
    }
}

/**
 * Create a new reservation
 * @param {object} reservationData - Reservation details
 * @returns {Promise} - Reservation response
 */
async function createReservation(reservationData) {
    try {
        const response = await apiRequest(
            API_CONFIG.ENDPOINTS.CREAR_RESERVA,
            {
                method: 'POST',
                body: JSON.stringify(reservationData)
            },
            API_CONFIG.TIMEOUT.RESERVATION
        );
        
        return response;
        
    } catch (error) {
        console.error('Error creating reservation:', error);
        throw error;
    }
}

/**
 * Search for existing client
 * @param {string} searchTerm - Email or phone to search
 * @returns {Promise} - Client search response
 */
async function searchClient(searchTerm) {
    try {
        const queryParams = new URLSearchParams({
            q: searchTerm
        });
        
        const response = await apiRequest(`${API_CONFIG.ENDPOINTS.BUSCAR_CLIENTE}?${queryParams}`);
        return response;
        
    } catch (error) {
        console.error('Error searching client:', error);
        throw error;
    }
}

// ===== WHATSAPP CONFIGURATION =====

const WHATSAPP_CONFIG = {
    // Replace with actual WhatsApp number (include country code without +)
    PHONE_NUMBER: '573001234567', // TODO: Configurar con número real del hotel
    
    // Default messages
    MESSAGES: {
        GENERAL_INQUIRY: 'Hola! Me interesa información sobre Casa Vieja Hotel y Restaurante.',
        RESERVATION_INQUIRY: 'Hola! Me gustaría hacer una reserva en Casa Vieja Hotel y Restaurante.',
        CONTACT_FORM: 'Hola! Te escribo desde la página web de Casa Vieja Hotel.'
    }
};

/**
 * Generate WhatsApp URL with pre-filled message
 * @param {string} message - Message to send
 * @param {string} source - Source of the message (for tracking)
 * @returns {string} - WhatsApp URL
 */
function generateWhatsAppURL(message, source = 'landing') {
    const encodedMessage = encodeURIComponent(`${message}\n\n(Enviado desde: ${source})`);
    return `https://wa.me/${WHATSAPP_CONFIG.PHONE_NUMBER}?text=${encodedMessage}`;
}

// ===== CONTACT INFORMATION =====

const CONTACT_INFO = {
    HOTEL_NAME: 'Casa Vieja Hotel y Restaurante',
    PHONE: '+57 (8) 123-4567',
    EMAIL: 'info@casaviejahotel.com',
    ADDRESS: 'Vereda La Montaña, Km 15 vía a Villa de Leyva',
    WEBSITE: 'https://casaviejahotel.com',
    
    // Social media
    SOCIAL: {
        FACEBOOK: 'https://facebook.com/casaviejahotel',
        INSTAGRAM: 'https://instagram.com/casaviejahotel',
        TRIPADVISOR: 'https://tripadvisor.com/casaviejahotel'
    },
    
    // Business hours
    HOURS: {
        RECEPTION: {
            CHECKIN: '15:00',
            CHECKOUT: '12:00'
        },
        RESTAURANT: {
            BREAKFAST: '07:00 - 10:00',
            LUNCH: '12:00 - 15:00',
            DINNER: '18:00 - 21:00'
        }
    }
};

// ===== ROOM TYPES CONFIGURATION =====

const ROOM_TYPES = {
    'estandar': {
        name: 'Habitación Estándar',
        capacity: 2,
        basePrice: 120000,
        amenities: ['Baño privado', 'WiFi', 'Vista al jardín', 'TV'],
        description: 'Habitación cómoda y acogedora con todas las comodidades básicas.'
    },
    'deluxe': {
        name: 'Habitación Deluxe',
        capacity: 3,
        basePrice: 180000,
        amenities: ['Baño privado', 'WiFi', 'Vista a la montaña', 'TV', 'Balcón', 'Minibar'],
        description: 'Habitación espaciosa con vista panorámica a la montaña y balcón privado.'
    },
    'suite': {
        name: 'Suite Familiar',
        capacity: 6,
        basePrice: 280000,
        amenities: ['2 Baños', 'WiFi', 'Sala de estar', 'TV', 'Terraza privada', 'Cocina básica'],
        description: 'Amplia suite ideal para familias con sala de estar y terraza privada.'
    }
};

// ===== PROMOTIONAL PACKAGES =====

const PROMOTIONAL_PACKAGES = {
    'romantico': {
        name: 'Fin de Semana Romántico',
        discount: 15,
        description: 'Cena especial, decoración romántica y vista privilegiada',
        includes: ['Cena romántica', 'Decoración especial', 'Botella de vino', 'Desayuno en la habitación'],
        validDays: ['friday', 'saturday'],
        minNights: 2
    },
    'familiar': {
        name: 'Plan Familiar',
        discount: 20,
        description: 'Habitaciones conectadas y actividades para toda la familia',
        includes: ['Actividades familiares', 'Descuento en restaurante', 'Tour guiado'],
        validDays: ['friday', 'saturday', 'sunday'],
        minNights: 2
    },
    'aventura': {
        name: 'Aventura en la Montaña',
        discount: 10,
        description: 'Incluye senderismo guiado y fogata nocturna',
        includes: ['Senderismo guiado', 'Fogata nocturna', 'Almuerzo campestre'],
        validDays: ['saturday', 'sunday'],
        minNights: 1
    }
};

// ===== UTILITY FUNCTIONS =====

/**
 * Calculate total price including taxes and discounts
 * @param {string} roomType - Room type key
 * @param {number} nights - Number of nights
 * @param {string} packageType - Package type (optional)
 * @returns {object} - Price breakdown
 */
function calculatePrice(roomType, nights, packageType = null) {
    const room = ROOM_TYPES[roomType];
    if (!room) {
        throw new Error('Invalid room type');
    }
    
    let basePrice = room.basePrice * nights;
    let discount = 0;
    let discountAmount = 0;
    
    if (packageType && PROMOTIONAL_PACKAGES[packageType]) {
        const package = PROMOTIONAL_PACKAGES[packageType];
        discount = package.discount;
        discountAmount = (basePrice * discount) / 100;
    }
    
    const subtotal = basePrice - discountAmount;
    const taxes = subtotal * 0.19; // 19% IVA
    const total = subtotal + taxes;
    
    return {
        basePrice,
        discount,
        discountAmount,
        subtotal,
        taxes,
        total,
        currency: 'COP'
    };
}

/**
 * Format price for display
 * @param {number} amount - Amount to format
 * @param {string} currency - Currency code
 * @returns {string} - Formatted price
 */
function formatPrice(amount, currency = 'COP') {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Validate date range
 * @param {string} checkin - Check-in date
 * @param {string} checkout - Check-out date
 * @returns {object} - Validation result
 */
function validateDateRange(checkin, checkout) {
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const errors = [];
    
    if (checkinDate < today) {
        errors.push('La fecha de llegada no puede ser anterior a hoy');
    }
    
    if (checkoutDate <= checkinDate) {
        errors.push('La fecha de salida debe ser posterior a la fecha de llegada');
    }
    
    const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
    
    return {
        valid: errors.length === 0,
        errors,
        nights
    };
}

// ===== EXPORT CONFIGURATION =====
// Make configurations available globally
window.API_CONFIG = API_CONFIG;
window.WHATSAPP_CONFIG = WHATSAPP_CONFIG;
window.CONTACT_INFO = CONTACT_INFO;
window.ROOM_TYPES = ROOM_TYPES;
window.PROMOTIONAL_PACKAGES = PROMOTIONAL_PACKAGES;

// Export utility functions
window.apiRequest = apiRequest;
window.checkAvailability = checkAvailability;
window.createReservation = createReservation;
window.searchClient = searchClient;
window.generateWhatsAppURL = generateWhatsAppURL;
window.calculatePrice = calculatePrice;
window.formatPrice = formatPrice;
window.validateDateRange = validateDateRange;

console.log('Casa Vieja Hotel API Configuration loaded successfully!');