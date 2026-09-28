/**
 * Discora - Product Details Page JavaScript Controller
 * Thumbnail gallery switcher, quantity selector, add to cart & review handler
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Thumbnail Image Gallery Switcher
    const mainImg = document.getElementById('mainProductImg');
    const thumbItems = document.querySelectorAll('.thumb-gallery .thumb-item');

    thumbItems.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbItems.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const newSrc = this.getAttribute('data-src');
            if (mainImg && newSrc) {
                if (typeof gsap !== 'undefined') {
                    gsap.to(mainImg, {
                        opacity: 0,
                        duration: 0.15,
                        onComplete: () => {
                            mainImg.src = newSrc;
                            gsap.to(mainImg, { opacity: 1, duration: 0.25 });
                        }
                    });
                } else {
                    mainImg.src = newSrc;
                }
            }
        });
    });

    // 2. Quantity Increment / Decrement Selector
    const qtyInput = document.getElementById('detailProductQty');
    const minusBtn = document.getElementById('qtyMinusBtn');
    const plusBtn  = document.getElementById('qtyPlusBtn');

    if (qtyInput && minusBtn && plusBtn) {
        const maxStock = parseInt(qtyInput.getAttribute('max'), 10) || 10;
        const minVal   = 1;

        minusBtn.addEventListener('click', () => {
            let val = parseInt(qtyInput.value, 10) || 1;
            if (val > minVal) {
                qtyInput.value = val - 1;
            }
        });

        plusBtn.addEventListener('click', () => {
            let val = parseInt(qtyInput.value, 10) || 1;
            if (val < maxStock) {
                qtyInput.value = val + 1;
            }
        });

        qtyInput.addEventListener('change', () => {
            let val = parseInt(qtyInput.value, 10) || 1;
            if (val < minVal) qtyInput.value = minVal;
            if (val > maxStock) qtyInput.value = maxStock;
        });
    }

    // 3. Add to Cart on Details Page
    const detailAddBtn = document.querySelector('.btn-add-cart-detail');
    if (detailAddBtn) {
        detailAddBtn.addEventListener('click', function() {
            const productId = parseInt(this.getAttribute('data-product-id'), 10);
            const quantity = parseInt(qtyInput?.value, 10) || 1;
            addToCart(productId, quantity, this);
        });
    }

    // 4. Review Form Submission AJAX
    const reviewForm = document.getElementById('productReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.require_auth) {
                    showToast(data.message, 'danger');
                    const modalEl = document.getElementById('writeReviewModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance?.hide();
                    openDiscoraAuthModal('login');
                    return;
                }

                if (data.success) {
                    showToast(data.message || 'Review submitted successfully!', 'success');
                    const modalEl = document.getElementById('writeReviewModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance?.hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to submit review.', 'danger');
                }
            } catch (err) {
                console.error('Review submission error:', err);
                showToast('Network error while submitting review.', 'danger');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // 5. GSAP Entrance for Product Details
    if (typeof gsap !== 'undefined') {
        gsap.fromTo('.product-gallery-sticky', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.5, ease: 'power2.out' });
        gsap.fromTo('.product-details-content', { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.5, delay: 0.1, ease: 'power2.out' });
    }
});
