/**
 * Navigation and Smooth Scrolling Fixes
 * Mejoras específicas para la navegación de la landing page
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== ELEMENTOS DE NAVEGACIÓN =====
    const navToggle = document.getElementById('nav-toggle');
    const navClose = document.getElementById('nav-close');
    const navMenu = document.getElementById('nav-menu');
    const navOverlay = document.getElementById('nav-overlay');
    const navLinks = document.querySelectorAll('.nav__link');
    const body = document.body;

    // ===== FUNCIONES DE MENÚ MÓVIL =====
    
    // Mostrar menú
    function showMenu() {
        navMenu?.classList.add('show-menu');
        body.classList.add('menu-open');
        navOverlay?.classList.add('active');
    }

    // Ocultar menú
    function hideMenu() {
        navMenu?.classList.remove('show-menu');
        body.classList.remove('menu-open');
        navOverlay?.classList.remove('active');
    }

    // Event Listeners para el menú
    navToggle?.addEventListener('click', showMenu);
    navClose?.addEventListener('click', hideMenu);
    navOverlay?.addEventListener('click', hideMenu);

    // Cerrar menú con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navMenu?.classList.contains('show-menu')) {
            hideMenu();
        }
    });

    // ===== NAVEGACIÓN SUAVE (SMOOTH SCROLLING) =====
    
    function smoothScrollTo(target, duration = 800) {
        const targetElement = document.querySelector(target);
        if (!targetElement) return;

        const header = document.getElementById('header');
        const headerHeight = header ? header.offsetHeight : 80;
        const targetPosition = targetElement.offsetTop - headerHeight - 20; // 20px extra de margen
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function animation(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
            window.scrollTo(0, run);
            if (timeElapsed < duration) requestAnimationFrame(animation);
        }

        // Función de easing
        function easeInOutQuad(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return c / 2 * t * t + b;
            t--;
            return -c / 2 * (t * (t - 2) - 1) + b;
        }

        requestAnimationFrame(animation);
    }

    // Manejar clicks en enlaces de navegación
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href && href.startsWith('#')) {
                e.preventDefault();
                
                // Ocultar menú móvil si está abierto
                hideMenu();
                
                // Hacer scroll suave
                smoothScrollTo(href);
                
                // Actualizar URL sin recargar página
                if (history.pushState) {
                    history.pushState(null, null, href);
                }
            }
        });
    });

    // También aplicar a otros enlaces que apunten a secciones
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        // Evitar duplicar event listeners
        if (link.classList.contains('nav__link')) return;
        
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.length > 1) { // No aplicar a href="#" vacío
                e.preventDefault();
                smoothScrollTo(href);
            }
        });
    });

    // ===== DESTACAR SECCIÓN ACTIVA EN NAVEGACIÓN =====
    
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav__link');
        
        let current = '';
        const scrollY = window.pageYOffset;
        const headerHeight = document.getElementById('header')?.offsetHeight || 80;

        sections.forEach(section => {
            const sectionTop = section.offsetTop - headerHeight - 100;
            const sectionHeight = section.offsetHeight;
            
            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    // Actualizar navegación activa en scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(updateActiveNavLink, 50);
    });

    // ===== MEJORAS DE UX =====
    
    // Prevenir scroll del body cuando el menú está abierto
    let scrollPosition = 0;
    
    function disableScroll() {
        scrollPosition = window.pageYOffset;
        body.style.position = 'fixed';
        body.style.top = `-${scrollPosition}px`;
        body.style.width = '100%';
    }

    function enableScroll() {
        body.style.position = '';
        body.style.top = '';
        body.style.width = '';
        window.scrollTo(0, scrollPosition);
    }

    // Aplicar disable scroll cuando se abre el menú
    const originalShowMenu = showMenu;
    showMenu = function() {
        originalShowMenu();
        disableScroll();
    };

    const originalHideMenu = hideMenu;
    hideMenu = function() {
        originalHideMenu();
        enableScroll();
    };

    // ===== INICIALIZACIÓN =====
    
    // Verificar si hay un hash en la URL al cargar la página
    if (window.location.hash) {
        setTimeout(() => {
            smoothScrollTo(window.location.hash);
        }, 100);
    }

    // Marcar enlace activo inicial
    updateActiveNavLink();
    
    console.log('🔧 Navigation fixes loaded successfully');
});

// ===== ESTILOS ADICIONALES VIA JAVASCRIPT =====
// Agregar estilos para la navegación activa
const navigationStyles = document.createElement('style');
navigationStyles.textContent = `
    .nav__link.active {
        color: var(--primary-color, #cc7710) !important;
        font-weight: 600;
    }
    
    .nav__link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--primary-color, #cc7710);
        border-radius: 1px;
    }
    
    @media (max-width: 768px) {
        .nav__link.active::after {
            display: none;
        }
        
        .nav__link.active {
            background-color: rgba(204, 119, 16, 0.1);
            border-radius: 4px;
            padding-left: 0.75rem !important;
            position: relative;
        }
        
        .nav__link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 3px;
            height: 20px;
            background-color: var(--primary-color, #cc7710);
            border-radius: 0 2px 2px 0;
            transform: translateY(-50%);
        }
    }
`;
document.head.appendChild(navigationStyles);
