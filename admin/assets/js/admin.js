/**
 * Discora - Admin JavaScript
 */
document.addEventListener("DOMContentLoaded", () => {
    
    // Notification Polling
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const noNotifications = document.getElementById('noNotifications');
    
    function fetchNotifications() {
        if (!notificationBadge) return;
        // We assume ADMIN_URL is accessible or we use a relative path
        fetch('actions/fetch-notifications.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.count > 0) {
                        notificationBadge.textContent = data.count;
                        notificationBadge.classList.remove('d-none');
                        
                        // GSAP bounce animation on badge if it's new
                        if (typeof gsap !== 'undefined') {
                            gsap.fromTo(notificationBadge, { scale: 0 }, { scale: 1, duration: 0.5, ease: "back.out(1.7)" });
                        }
                        
                        let html = '';
                        data.notifications.forEach(notif => {
                            let icon = notif.type === 'New Order' ? 'bi-cart-plus text-success' : 'bi-envelope text-info';
                            let link = notif.type === 'New Order' ? `order-details.php?id=${notif.reference_id}` : '#';
                            html += `
                                <li class="border-bottom">
                                    <a class="dropdown-item d-flex align-items-start p-3" href="${link}">
                                        <i class="bi ${icon} fs-4 me-3"></i>
                                        <div>
                                            <p class="mb-1 text-wrap fw-bold">${notif.message}</p>
                                            <small class="text-muted">${notif.created_at}</small>
                                        </div>
                                    </a>
                                </li>
                            `;
                        });
                        notificationList.innerHTML = html;
                    } else {
                        notificationBadge.classList.add('d-none');
                        notificationList.innerHTML = '<li class="p-3 text-center text-muted small" id="noNotifications">No new notifications</li>';
                    }
                }
            })
            .catch(err => console.error("Notification polling failed", err));
    }
    
    // Check initially
    fetchNotifications();
    // Poll every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Mark as read when clicking the bell
    if (notificationDropdown) {
        notificationDropdown.addEventListener('show.bs.dropdown', () => {
            if (!notificationBadge.classList.contains('d-none')) {
                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'mark_read');
                
                fetch('actions/fetch-notifications.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    notificationBadge.classList.add('d-none');
                    notificationBadge.textContent = '0';
                });
            }
        });
    }

    // GSAP Sidebar Animations (Hover effects)
    const navLinks = document.querySelectorAll('.nav-link');
    if (typeof gsap !== 'undefined') {
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                if (!link.classList.contains('active')) {
                    gsap.to(link, { x: 5, duration: 0.2, backgroundColor: "rgba(255,255,255,0.05)" });
                }
            });
            link.addEventListener('mouseleave', () => {
                if (!link.classList.contains('active')) {
                    gsap.to(link, { x: 0, duration: 0.2, backgroundColor: "transparent" });
                }
            });
        });
    }

});
