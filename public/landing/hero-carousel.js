/**
 * Hero Carousel Controller
 */

document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('hero-carousel');
    const slides = document.querySelectorAll('.hero__slide');
    const indicators = document.querySelectorAll('.hero__indicator');
    const prevBtn = document.getElementById('hero-prev');
    const nextBtn = document.getElementById('hero-next');
    const textContents = document.querySelectorAll('.hero__text-content');
    
    if (!carousel || slides.length <= 1) {
        console.log('Hero carousel: No hay suficientes slides');
        return;
    }
    
    let currentSlide = 0;
    let isAnimating = false;
    let autoplayInterval;
    const autoplayDelay = 6000;
    
    function goToSlide(slideIndex) {
        if (isAnimating || slideIndex === currentSlide) return;
        
        isAnimating = true;
        
        // Remover clases activas
        slides[currentSlide].classList.remove('active');
        indicators[currentSlide]?.classList.remove('active');
        textContents[currentSlide]?.classList.remove('active');
        
        // Actualizar índice
        currentSlide = slideIndex;
        
        // Activar nuevos elementos
        slides[currentSlide].classList.add('active');
        indicators[currentSlide]?.classList.add('active');
        textContents[currentSlide]?.classList.add('active');
        
        setTimeout(() => {
            isAnimating = false;
        }, 1000);
        
        resetAutoplay();
        console.log('Carrusel: slide ' + (currentSlide + 1) + '/' + slides.length);
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }
    
    function startAutoplay() {
        if (slides.length <= 1) return;
        autoplayInterval = setInterval(nextSlide, autoplayDelay);
    }
    
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }
    
    function resetAutoplay() {
        stopAutoplay();
        startAutoplay();
    }
    
    // Event listeners
    nextBtn?.addEventListener('click', nextSlide);
    prevBtn?.addEventListener('click', prevSlide);
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => goToSlide(index));
    });
    
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    
    // Control con teclado
    document.addEventListener('keydown', (e) => {
        const heroSection = document.getElementById('inicio');
        const rect = heroSection?.getBoundingClientRect();
        const isVisible = rect && rect.top < window.innerHeight && rect.bottom > 0;
        
        if (!isVisible) return;
        
        if (e.key === 'ArrowRight' || e.key === ' ') {
            e.preventDefault();
            nextSlide();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prevSlide();
        }
    });
    
    // Inicialización
    slides[0]?.classList.add('active');
    indicators[0]?.classList.add('active');
    textContents[0]?.classList.add('active');
    
    startAutoplay();
    
    console.log('Hero Carousel inicializado: ' + slides.length + ' slides');
});