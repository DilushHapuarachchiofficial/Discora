/**
 * Discora - Homepage Interactive Multi-Item Carousel
 */

document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('newArrivalsTrack');
    const prevBtn = document.getElementById('carouselPrevBtn');
    const nextBtn = document.getElementById('carouselNextBtn');
    const viewport = document.querySelector('.carousel-viewport');

    if (!track || !prevBtn || !nextBtn || !viewport) return;

    let currentIndex = 0;
    let autoPlayTimer = null;
    const cards = track.querySelectorAll('.carousel-item-wrapper');
    const totalCards = cards.length;

    function getItemsPerView() {
        const width = window.innerWidth;
        if (width < 768) return 1;
        if (width < 992) return 2;
        if (width < 1200) return 3;
        return 4;
    }

    function getMaxIndex() {
        const itemsPerView = getItemsPerView();
        return Math.max(0, totalCards - itemsPerView);
    }

    function getGapSize() {
        const style = window.getComputedStyle(track);
        const gapVal = parseFloat(style.gap || style.columnGap || '24');
        return isNaN(gapVal) ? 24 : gapVal;
    }

    function updateCarousel() {
        const maxIndex = getMaxIndex();

        if (currentIndex > maxIndex) {
            currentIndex = 0;
        }

        const card = cards[0];
        if (!card) return;

        // Dynamic width and gap calculation
        const cardWidth = card.offsetWidth;
        const gap = getGapSize();
        const moveAmount = (cardWidth + gap) * currentIndex;

        track.style.transform = `translateX(-${moveAmount}px)`;

        // Update button visual states
        if (prevBtn) {
            prevBtn.style.opacity = currentIndex === 0 ? '0.4' : '1';
            prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
        }
        if (nextBtn) {
            nextBtn.style.opacity = currentIndex >= maxIndex ? '0.4' : '1';
            nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
        }
    }

    function slideNext() {
        const maxIndex = getMaxIndex();
        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            currentIndex = 0;
        }
        updateCarousel();
    }

    function slidePrev() {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = getMaxIndex();
        }
        updateCarousel();
    }

    nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        slideNext();
        resetAutoPlay();
    });

    prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        slidePrev();
        resetAutoPlay();
    });

    // Auto Play Functionality
    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(slideNext, 5000);
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }

    viewport.addEventListener('mouseenter', stopAutoPlay);
    viewport.addEventListener('mouseleave', startAutoPlay);

    // Touch / Swipe Gesture Support for Mobile & Tablet
    let touchStartX = 0;
    let touchEndX = 0;

    viewport.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    viewport.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoPlay();
    }, { passive: true });

    function handleSwipe() {
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 35) {
            if (diff > 0) {
                slideNext();
            } else {
                slidePrev();
            }
        }
    }

    // Debounced Resize Recalculation
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateCarousel();
        }, 100);
    });

    // Initial Trigger after images and fonts load
    window.addEventListener('load', () => {
        updateCarousel();
    });
    updateCarousel();
    startAutoPlay();
});
