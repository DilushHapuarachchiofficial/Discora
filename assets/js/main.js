/**
 * Discora - Main Universal JavaScript (Navigation, Side Drawer, Smooth Scrolling)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Header Scroll Effect
    const siteHeader = document.getElementById('siteHeader');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 40) {
            siteHeader?.classList.add('scrolled');
        } else {
            siteHeader?.classList.remove('scrolled');
        }
    });

    // 2. Mobile Side Navigation Drawer (GSAP Powered)
    const mobileNav = document.getElementById('mobileSideNav');
    const navOverlay = document.getElementById('sideNavOverlay');
    const openBtn = document.getElementById('mobileMenuToggle');
    const closeBtn = document.getElementById('sideNavCloseBtn');
    let isNavOpen = false;

    function openMobileNav() {
        if (isNavOpen || !mobileNav) return;
        isNavOpen = true;
        document.body.classList.add('nav-open');
        navOverlay?.classList.add('active');

        if (typeof gsap !== 'undefined') {
            gsap.to(mobileNav, {
                right: '0%',
                duration: 0.4,
                ease: 'power3.out'
            });
            gsap.fromTo('.side-nav-links .side-nav-item', 
                { opacity: 0, x: 25 },
                { opacity: 1, x: 0, duration: 0.35, stagger: 0.05, ease: 'power2.out', delay: 0.1 }
            );
        } else {
            mobileNav.style.right = '0%';
        }
    }

    function closeMobileNav() {
        if (!isNavOpen || !mobileNav) return;
        isNavOpen = false;
        document.body.classList.remove('nav-open');
        navOverlay?.classList.remove('active');

        if (typeof gsap !== 'undefined') {
            gsap.to(mobileNav, {
                right: '-100%',
                duration: 0.35,
                ease: 'power3.in'
            });
        } else {
            mobileNav.style.right = '-100%';
        }
    }

    openBtn?.addEventListener('click', openMobileNav);
    closeBtn?.addEventListener('click', closeMobileNav);
    navOverlay?.addEventListener('click', closeMobileNav);

    // Close mobile nav on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isNavOpen) {
            closeMobileNav();
        }
    });

    // 3. Smooth Scrolling for Navigation Links with Header Offset
    const scrollLinks = document.querySelectorAll('.nav-scroll-link');
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (!href) return;

            // Check if link points to an on-page hash ID
            const hashIndex = href.indexOf('#');
            if (hashIndex !== -1) {
                const targetId = href.substring(hashIndex);
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    e.preventDefault();
                    closeMobileNav();

                    const headerOffset = siteHeader ? siteHeader.offsetHeight + 10 : 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Update active link state
                    scrollLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            }
        });
    });

    // Initialize Bootstrap tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
});
