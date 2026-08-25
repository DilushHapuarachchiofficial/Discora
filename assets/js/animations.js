/**
 * Discora - GSAP & ScrollTrigger Animation Controller
 */

document.addEventListener('DOMContentLoaded', () => {
    // Check if GSAP is available
    if (typeof gsap === 'undefined') {
        console.warn('GSAP library not detected.');
        return;
    }

    // Register ScrollTrigger plugin
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // Check user accessibility preference for reduced motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        console.log('Reduced motion preferred. Skipping heavy animations.');
        return;
    }

    // ---------------------------------------------------------
    // 1. Hero Banner Animations (On Page Load)
    // ---------------------------------------------------------
    const heroTl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    heroTl
        .fromTo('.hero-bg-img', 
            { scale: 1.1, opacity: 0 }, 
            { scale: 1, opacity: 1, duration: 1.2, ease: 'power2.out' }
        )
        .fromTo('.hero-title', 
            { y: 40, opacity: 0 }, 
            { y: 0, opacity: 1, duration: 0.9 }, 
            '-=0.7'
        )
        .fromTo('.hero-subtitle', 
            { y: 25, opacity: 0 }, 
            { y: 0, opacity: 1, duration: 0.7 }, 
            '-=0.5'
        )
        .fromTo('.hero-cta-btn', 
            { y: 20, opacity: 0, scale: 0.95 }, 
            { y: 0, opacity: 1, scale: 1, duration: 0.6, stagger: 0.15 }, 
            '-=0.4'
        );

    // Subtle Hero Mouse Movement / Parallax on Desktop
    const heroSection = document.querySelector('.hero-section');
    if (heroSection && window.innerWidth > 992) {
        heroSection.addEventListener('mousemove', (e) => {
            const { clientX, clientY } = e;
            const xPos = (clientX / window.innerWidth - 0.5) * 18;
            const yPos = (clientY / window.innerHeight - 0.5) * 18;

            gsap.to('.hero-bg-img', {
                x: xPos,
                y: yPos,
                duration: 1.5,
                ease: 'power1.out'
            });
        });
    }

    // ---------------------------------------------------------
    // 2. New Arrivals Section Scroll Reveal
    // ---------------------------------------------------------
    if (document.querySelector('#new-arrivals')) {
        gsap.from('#new-arrivals .section-headline, #new-arrivals .section-desc-light', {
            scrollTrigger: {
                trigger: '#new-arrivals',
                start: 'top 80%',
                toggleActions: 'play none none none'
            },
            y: 35,
            opacity: 0,
            duration: 0.8,
            stagger: 0.2,
            ease: 'power2.out'
        });

        gsap.from('.carousel-item-card', {
            scrollTrigger: {
                trigger: '.carousel-outer-wrapper',
                start: 'top 85%',
                toggleActions: 'play none none none'
            },
            y: 50,
            opacity: 0,
            scale: 0.95,
            duration: 0.7,
            stagger: 0.12,
            ease: 'power2.out'
        });
    }

    // ---------------------------------------------------------
    // 3. PlayStation Showcase Section Scroll Reveal
    // ---------------------------------------------------------
    if (document.querySelector('#playstation')) {
        gsap.from('#playstation .ps-showcase-img', {
            scrollTrigger: {
                trigger: '#playstation',
                start: 'top 75%',
                toggleActions: 'play none none none'
            },
            x: -60,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });

        gsap.from('#playstation .ps-content-col > *', {
            scrollTrigger: {
                trigger: '#playstation',
                start: 'top 75%',
                toggleActions: 'play none none none'
            },
            x: 50,
            opacity: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'power3.out'
        });
    }

    // ---------------------------------------------------------
    // 4. Xbox Showcase Section Scroll Reveal
    // ---------------------------------------------------------
    if (document.querySelector('#xbox')) {
        gsap.from('#xbox .xbox-content-col > *', {
            scrollTrigger: {
                trigger: '#xbox',
                start: 'top 75%',
                toggleActions: 'play none none none'
            },
            x: -50,
            opacity: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'power3.out'
        });

        gsap.from('#xbox .xbox-showcase-img', {
            scrollTrigger: {
                trigger: '#xbox',
                start: 'top 75%',
                toggleActions: 'play none none none'
            },
            x: 60,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });
    }

    // ---------------------------------------------------------
    // 5. About Us Section Scroll Reveal
    // ---------------------------------------------------------
    if (document.querySelector('#about-us')) {
        gsap.from('#about-us .about-header-badge, #about-us .about-punchline, #about-us .section-desc-dark', {
            scrollTrigger: {
                trigger: '#about-us',
                start: 'top 80%',
                toggleActions: 'play none none none'
            },
            y: 35,
            opacity: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'power2.out'
        });

        gsap.from('#about-us .feature-col', {
            scrollTrigger: {
                trigger: '#about-us .features-row',
                start: 'top 85%',
                toggleActions: 'play none none none'
            },
            y: 40,
            opacity: 0,
            duration: 0.7,
            stagger: 0.15,
            ease: 'power2.out'
        });
    }

    // ---------------------------------------------------------
    // 6. Contact Us Section Scroll Reveal
    // ---------------------------------------------------------
    if (document.querySelector('#contact-us')) {
        gsap.from('#contact-us .contact-header-badge, #contact-us .contact-punchline, #contact-us .section-desc-light', {
            scrollTrigger: {
                trigger: '#contact-us',
                start: 'top 80%',
                toggleActions: 'play none none none'
            },
            y: 35,
            opacity: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'power2.out'
        });

        gsap.from('#contact-us .contact-info-col', {
            scrollTrigger: {
                trigger: '#contact-us .contact-info-row',
                start: 'top 85%',
                toggleActions: 'play none none none'
            },
            y: 40,
            opacity: 0,
            duration: 0.7,
            stagger: 0.15,
            ease: 'power2.out'
        });
    }
});
