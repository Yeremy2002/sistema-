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
            confirmButtonColor: '#cc7710',
            timer: 5000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
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
            confirmButtonColor: '#cc7710',
            showClass: {
                popup: 'animate__animated animate__shakeX'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
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
            confirmButtonColor: '#cc7710',
            showClass: {
                popup: 'animate__animated animate__pulse'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
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
            confirmButtonColor: '#cc7710',
            timer: 7000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
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
        confirmButtonColor: '#cc7710',
        showClass: {
            popup: 'animate__animated animate__bounceIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
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
