/**
 * Discora - Unified Shopping Cart & Wishlist AJAX Controller
 */

// Toast notification helper
function showToast(message, type = 'success') {
    let container = document.getElementById('discoraToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'discoraToastContainer';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-dark border border-${type === 'success' ? 'primary' : 'danger'} shadow-lg`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    const icon = type === 'success' ? 'bi-check-circle-fill text-primary' : 'bi-exclamation-circle-fill text-danger';

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi ${icon} fs-5"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

/**
 * Global Add to Cart Function
 */
async function addToCart(productId, quantity = 1, btn = null) {
    if (!productId || productId <= 0) return;

    let originalHtml = '';
    if (btn) {
        originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const response = await fetch('actions/cart-action.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (data.success) {
            // Update Cart Badge Count
            const badge = document.getElementById('cart-badge-count');
            if (badge) {
                badge.textContent = data.cart_count;
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(badge, { scale: 1.6 }, { scale: 1, duration: 0.35, ease: 'back.out(2)' });
                }
            }

            showToast(data.message || 'Game added to your cart!', 'success');

            if (btn) {
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Added';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 1500);
            }
        } else {
            showToast(data.message || 'Failed to add item to cart.', 'danger');
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    } catch (err) {
        console.error('Cart action error:', err);
        showToast('Network error while adding to cart.', 'danger');
        if (btn) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }
}

/**
 * Global Wishlist Toggle Function
 */
async function toggleWishlist(productId, btn = null) {
    if (!productId || productId <= 0) return;

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const response = await fetch('actions/wishlist-action.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (data.require_auth) {
            showToast(data.message, 'danger');
            if (typeof window.openDiscoraAuthModal === 'function') {
                window.openDiscoraAuthModal('login');
            }
            return;
        }

        if (data.success) {
            if (btn) {
                if (data.action === 'added') {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(btn, { scale: 1.3 }, { scale: 1, duration: 0.3, ease: 'back.out(2)' });
                }
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Could not update wishlist.', 'danger');
        }
    } catch (err) {
        console.error('Wishlist error:', err);
    }
}

// Global Event Listeners for Cart and Wishlist triggers
document.addEventListener('DOMContentLoaded', () => {
    // 1. Add to Cart button clicks on cards
    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('.btn-add-cart');
        if (addBtn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = parseInt(addBtn.dataset.productId, 10);
            addToCart(productId, 1, addBtn);
        }
    });

    // 2. Wishlist button clicks
    document.addEventListener('click', (e) => {
        const wishBtn = e.target.closest('.btn-wishlist-toggle');
        if (wishBtn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = parseInt(wishBtn.dataset.productId, 10);
            toggleWishlist(productId, wishBtn);
        }
    });
});
