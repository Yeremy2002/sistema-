/**
 * SweetAlert2 Integration for Casa Vieja Hotel Landing Page
 * Replaces custom notifications with beautiful SweetAlert2 modals
 */

// Check if SweetAlert2 is available
if (typeof Swal === 'undefined') {
    console.error('❌ SweetAlert2 no está cargado. Las notificaciones pueden no funcionar correctamente.');
} else {
    console.log('✅ SweetAlert2 está disponible, integrando funciones...');
    
    // Enhanced success message
    window.showSuccessMessage = function(message) {
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC8711', // Color principal del hotel
            timer: 6000,
            timerProgressBar: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            backdrop: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                popup: 'swal-hotel-popup',
                title: 'swal-hotel-title',
                confirmButton: 'swal-hotel-button'
            }
        });
    };

    // Enhanced error message
    window.showErrorMessage = function(message) {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC8711',
            allowOutsideClick: true,
            allowEscapeKey: true,
            showClass: {
                popup: 'animate__animated animate__shakeX'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                popup: 'swal-hotel-popup',
                title: 'swal-hotel-title',
                confirmButton: 'swal-hotel-button'
            }
        });
    };

    // Enhanced warning message
    window.showWarningMessage = function(message) {
        Swal.fire({
            title: 'Atención',
            text: message,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC8711',
            allowOutsideClick: true,
            allowEscapeKey: true,
            showClass: {
                popup: 'animate__animated animate__pulse'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                popup: 'swal-hotel-popup',
                title: 'swal-hotel-title',
                confirmButton: 'swal-hotel-button'
            }
        });
    };

    // Enhanced info message
    window.showInfoMessage = function(message) {
        Swal.fire({
            title: 'Información',
            text: message,
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC8711',
            timer: 7000,
            timerProgressBar: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                popup: 'swal-hotel-popup',
                title: 'swal-hotel-title',
                confirmButton: 'swal-hotel-button'
            }
        });
    };

    // Enhanced notification for reservation success with more details
    window.showReservationSuccess = function(details) {
    const {
        roomName = 'Habitación',
        roomNumber = '',
        checkIn = '',
        checkOut = '',
        nights = 0,
        total = 0
    } = details;
    
    const htmlContent = `
        <div style="text-align: left; margin: 20px 0;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;">
                <h4 style="margin: 0 0 10px 0; color: #cc7710;">📋 Detalles de tu Reserva</h4>
                <p style="margin: 5px 0;"><strong>🏠 Habitación:</strong> ${roomName} ${roomNumber ? `#${roomNumber}` : ''}</p>
                <p style="margin: 5px 0;"><strong>📅 Llegada:</strong> ${checkIn}</p>
                <p style="margin: 5px 0;"><strong>📅 Salida:</strong> ${checkOut}</p>
                <p style="margin: 5px 0;"><strong>🌙 Noches:</strong> ${nights}</p>
                ${total > 0 ? `<p style="margin: 5px 0;"><strong>💰 Total estimado:</strong> $${total.toLocaleString()}</p>` : ''}
            </div>
            <p style="margin: 15px 0; color: #666;">Te contactaremos pronto para confirmar todos los detalles de tu estadía.</p>
        </div>
    `;
    
    Swal.fire({
        title: '¡Reserva Enviada Exitosamente!',
        html: htmlContent,
        icon: 'success',
        confirmButtonText: '¡Perfecto!',
        confirmButtonColor: '#DC8711',
        allowOutsideClick: true,
        allowEscapeKey: true,
        backdrop: true,
        showClass: {
            popup: 'animate__animated animate__bounceIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        },
        customClass: {
            popup: 'swal-hotel-popup swal-reservation-success',
            title: 'swal-hotel-title',
            confirmButton: 'swal-hotel-button',
            htmlContainer: 'swal-reservation-details'
        },
        width: '90%',
        maxWidth: '500px'
    });
    };

    // Loading modal for reservation process
    window.showReservationLoading = function(message = 'Procesando tu reserva...') {
        Swal.fire({
            title: 'Un momento por favor',
            text: message,
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    // Close any open SweetAlert2 modal
    window.closeSweetAlert = function() {
        Swal.close();
    };

    // Confirmation dialog for important actions
    window.showConfirmation = function(title, text, confirmButtonText = 'Sí, continuar') {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#cc7710',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancelar',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    };

    // Override the original showNotification function
    window.showNotification = function(message, type = 'info') {
        switch (type) {
            case 'success':
                showSuccessMessage(message);
                break;
            case 'error':
                showErrorMessage(message);
                break;
            case 'warning':
                showWarningMessage(message);
                break;
            case 'info':
            default:
                showInfoMessage(message);
                break;
        }
    };
    
    // Inyectar estilos CSS personalizados para SweetAlert2
    const swalCustomStyles = document.createElement('style');
    swalCustomStyles.textContent = `
        /* Estilos personalizados para SweetAlert2 - Casa Vieja Hotel */
        .swal-hotel-popup {
            border-radius: 12px !important;
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15) !important;
            font-family: 'Inter', sans-serif !important;
        }

        .swal-hotel-title {
            color: #664D07 !important;
            font-family: 'Playfair Display', serif !important;
            font-weight: 600 !important;
        }

        .swal-hotel-button {
            background-color: #DC8711 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
        }

        .swal-hotel-button:hover {
            background-color: #BEA572 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(220, 135, 17, 0.3) !important;
        }

        .swal-reservation-success {
            max-width: 500px !important;
        }

        .swal-reservation-details {
            text-align: left !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
        }

        .swal-reservation-details h4 {
            color: #DC8711 !important;
            font-family: 'Playfair Display', serif !important;
            margin-bottom: 10px !important;
        }

        .swal-reservation-details p {
            margin: 5px 0 !important;
            color: #333 !important;
        }

        /* Responsive para SweetAlert2 */
        @media (max-width: 768px) {
            .swal-hotel-popup {
                margin: 10px !important;
                max-width: calc(100% - 20px) !important;
            }

            .swal-hotel-title {
                font-size: 18px !important;
            }

            .swal-reservation-details {
                font-size: 13px !important;
            }
        }

        /* Z-index alto para estar sobre el modal - CRITICAL FIX */
        .swal2-container {
            z-index: 10000 !important;
        }

        .swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.6) !important;
            z-index: 9999 !important;
        }

        /* Force SweetAlert2 popup above everything */
        .swal2-popup {
            z-index: 10001 !important;
        }
    `;

    if (!document.head.querySelector('#swal-hotel-styles')) {
        swalCustomStyles.id = 'swal-hotel-styles';
        document.head.appendChild(swalCustomStyles);
    }

    console.log('✅ SweetAlert2 functions loaded successfully');
}

// Fallback functions if SweetAlert2 is not available
if (typeof Swal === 'undefined') {
    window.showSuccessMessage = window.showSuccessMessage || function(message) { alert(message); };
    window.showErrorMessage = window.showErrorMessage || function(message) { alert('Error: ' + message); };
    window.showWarningMessage = window.showWarningMessage || function(message) { alert('Atención: ' + message); };
    window.showInfoMessage = window.showInfoMessage || function(message) { alert(message); };
    window.showNotification = window.showNotification || function(message, type) { alert(message); };
}
