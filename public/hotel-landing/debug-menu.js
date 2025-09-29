// Debug JavaScript solo para el menú hamburguesa
console.log('Debug menu script loaded');

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Obtener elementos
    const navToggle = document.getElementById('nav-toggle');
    const navClose = document.getElementById('nav-close');
    const navMenu = document.getElementById('nav-menu');
    const navOverlay = document.getElementById('nav-overlay');
    
    console.log('Elements found:', {
        navToggle: !!navToggle,
        navClose: !!navClose,
        navMenu: !!navMenu,
        navOverlay: !!navOverlay
    });
    
    // Función para mostrar menú
    function showMenu() {
        console.log('showMenu called');
        if (navMenu) {
            navMenu.classList.add('show-menu');
            document.body.style.overflow = 'hidden';
            document.body.classList.add('nav-open');
        }
        if (navOverlay) {
            navOverlay.classList.add('active');
        }
    }
    
    // Función para ocultar menú
    function hideMenu() {
        console.log('hideMenu called');
        if (navMenu) {
            navMenu.classList.remove('show-menu');
            document.body.style.overflow = '';
            document.body.classList.remove('nav-open');
        }
        if (navOverlay) {
            navOverlay.classList.remove('active');
        }
    }
    
    // Event listeners
    if (navToggle) {
        navToggle.addEventListener('click', function(e) {
            console.log('Toggle clicked');
            e.preventDefault();
            showMenu();
        });
    }
    
    if (navClose) {
        navClose.addEventListener('click', function(e) {
            console.log('Close clicked');
            e.preventDefault();
            hideMenu();
        });
    }
    
    if (navOverlay) {
        navOverlay.addEventListener('click', function(e) {
            console.log('Overlay clicked');
            e.preventDefault();
            hideMenu();
        });
    }
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            console.log('Escape pressed');
            hideMenu();
        }
    });
    
    console.log('Debug menu initialized');
});
