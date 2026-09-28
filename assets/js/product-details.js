document.addEventListener('DOMContentLoaded', () => {
    // Quantity controls
    const qtyInput = document.getElementById('detailProductQty');
    const btnMinus = document.getElementById('qtyMinusBtn');
    const btnPlus = document.getElementById('qtyPlusBtn');

    if (qtyInput && btnMinus && btnPlus) {
        btnMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        
        btnPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            let max = parseInt(qtyInput.getAttribute('max')) || 99;
            if (val < max) qtyInput.value = val + 1;
        });
    }

    // Wishlist Toggle
    const wishlistBtns = document.querySelectorAll('.btn-wishlist-toggle');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = btn.dataset.productId;
            if (!productId) return;

            const icon = btn.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'bi bi-arrow-repeat spin'; // loading state

            try {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', 'toggle');

                const res = await fetch('actions/wishlist-action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    if (data.action === 'added') {
                        icon.className = 'bi bi-heart-fill text-danger';
                        btn.title = 'Remove from Wishlist';
                    } else if (data.action === 'removed') {
                        icon.className = 'bi bi-heart';
                        btn.title = 'Add to Wishlist';
                    } else {
                        icon.className = originalClass;
                    }
                } else {
                    icon.className = originalClass;
                    alert(data.message || 'Error updating wishlist');
                }
            } catch (err) {
                icon.className = originalClass;
                console.error(err);
            }
        });
    });

    // Main image gallery thumbnail swapping
    const thumbs = document.querySelectorAll('.thumb-item');
    const mainImg = document.getElementById('mainProductImg');
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            thumbs.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            if (mainImg) {
                mainImg.src = thumb.dataset.src;
            }
        });
    });
});
