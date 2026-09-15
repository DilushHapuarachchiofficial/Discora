        </div> <!-- End Admin Content -->
    </div> <!-- End Admin Main -->
</div> <!-- End Admin Layout -->

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('mobileMenuToggle');
        const desktopToggleBtn = document.getElementById('desktopMenuToggle');
        const closeBtn = document.getElementById('closeSidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.add('show');
            });
        }
        
        if (desktopToggleBtn) {
            desktopToggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('minimized');
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.remove('show');
            });
        }

        // GSAP entry animations for dashboard elements
        if (typeof gsap !== 'undefined') {
            gsap.from(".admin-content > .row > div", {
                y: 20,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: "power2.out"
            });
        }
    });
</script>
<script src="<?= ADMIN_URL ?>assets/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
