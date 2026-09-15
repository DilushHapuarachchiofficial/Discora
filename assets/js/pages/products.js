/**
 * Discora - New Arrivals & Catalog GSAP Motion & Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion || typeof gsap === 'undefined') return;

    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // 1. New Arrivals Hero Entrance Animation Sequence
    const heroSection = document.querySelector('.new-arrivals-hero');
    if (heroSection) {
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        // Container fade in
        tl.fromTo(heroSection, 
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 0.6 }
        );

        // Badge reveal
        const heroBadge = heroSection.querySelector('.hero-badge');
        if (heroBadge) {
            tl.fromTo(heroBadge,
                { opacity: 0, scale: 0.85 },
                { opacity: 1, scale: 1, duration: 0.4 },
                '-=0.3'
            );
        }

        // Main Title (NEW ARRIVALS)
        const heroTitle = heroSection.querySelector('.hero-title');
        if (heroTitle) {
            tl.fromTo(heroTitle,
                { opacity: 0, y: -20 },
                { opacity: 1, y: 0, duration: 0.5 },
                '-=0.2'
            );
        }

        // Secondary Highlight (HOT RELEASES)
        const heroSubtitle = heroSection.querySelector('.hero-subtitle');
        if (heroSubtitle) {
            tl.fromTo(heroSubtitle,
                { opacity: 0, x: -20 },
                { opacity: 1, x: 0, duration: 0.5 },
                '-=0.3'
            );
        }

        // Description
        const heroDesc = heroSection.querySelector('.hero-desc');
        if (heroDesc) {
            tl.fromTo(heroDesc,
                { opacity: 0, y: 15 },
                { opacity: 1, y: 0, duration: 0.5 },
                '-=0.3'
            );
        }

        // Key stats pills
        const heroStats = heroSection.querySelectorAll('.hero-stats > div');
        if (heroStats.length > 0) {
            tl.fromTo(heroStats,
                { opacity: 0, y: 15 },
                { opacity: 1, y: 0, duration: 0.4, stagger: 0.1 },
                '-=0.2'
            );
        }

        // Physical Disc Graphic Showcase
        const heroGraphic = heroSection.querySelector('.hero-physical-disc-showcase');
        if (heroGraphic) {
            tl.fromTo(heroGraphic,
                { opacity: 0, x: 40, scale: 0.95 },
                { opacity: 1, x: 0, scale: 1, duration: 0.7, ease: 'back.out(1.4)' },
                '-=0.6'
            );
        }

        // Subtle Ambient Rotation for Decorative Game Disc Shapes
        const discShapes = heroSection.querySelectorAll('.disc-shape');
        if (discShapes.length > 0) {
            gsap.to(discShapes, {
                rotation: 360,
                duration: 60,
                repeat: -1,
                ease: 'none'
            });
        }
    }

    // 2. Toolbar & Sidebar Entrance Animation
    const filterSidebar = document.querySelector('.filter-sidebar');
    const toolbar = document.querySelector('.catalog-toolbar');
    if (filterSidebar || toolbar) {
        gsap.fromTo([toolbar, filterSidebar].filter(Boolean),
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.3, ease: 'power2.out' }
        );
    }

    // 3. Staggered Product Cards Reveal with ScrollTrigger
    const productCards = document.querySelectorAll('.product-card-col');
    if (productCards.length > 0) {
        gsap.fromTo(productCards,
            { opacity: 0, y: 35, scale: 0.97 },
            {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.5,
                stagger: 0.06,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '#productGridContainer',
                    start: 'top 85%',
                    once: true
                }
            }
        );
    }

    // 4. GSAP Micro-Interactions on Card Hover
    document.querySelectorAll('.product-card').forEach((card) => {
        const img = card.querySelector('.product-img');
        const wishlistBtn = card.querySelector('.btn-wishlist-toggle');

        card.addEventListener('mouseenter', () => {
            if (img) {
                gsap.to(img, {
                    scale: 1.06,
                    y: -5,
                    duration: 0.35,
                    ease: 'power2.out'
                });
            }
            if (wishlistBtn) {
                gsap.to(wishlistBtn, {
                    scale: 1.1,
                    duration: 0.2
                });
            }
        });

        card.addEventListener('mouseleave', () => {
            if (img) {
                gsap.to(img, {
                    scale: 1,
                    y: 0,
                    duration: 0.35,
                    ease: 'power2.out'
                });
            }
            if (wishlistBtn) {
                gsap.to(wishlistBtn, {
                    scale: 1,
                    duration: 0.2
                });
            }
        });
    });
});
