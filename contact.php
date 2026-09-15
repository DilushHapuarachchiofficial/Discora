<?php
/**
 * Discora - Contact & Support Page
 */
$page_title = "Contact Support";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row g-5">
        <div class="col-lg-6">
            <h2 class="text-light fw-bold mb-3">Get in Touch</h2>
            <p class="text-secondary mb-4">Have questions about game availability, disc condition, or your order tracking? Drop us a message.</p>

            <div class="d-flex flex-column gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-dark p-3 rounded-circle text-primary"><i class="bi bi-geo-alt fs-5"></i></div>
                    <div>
                        <strong class="text-light d-block">Store Location</strong>
                        <span class="text-secondary small">742 Evergreen Gaming Blvd, Seattle, WA</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-dark p-3 rounded-circle text-primary"><i class="bi bi-envelope fs-5"></i></div>
                    <div>
                        <strong class="text-light d-block">Email Support</strong>
                        <span class="text-secondary small">support@discora.com</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card bg-dark border-secondary border-opacity-25 p-4 rounded-3">
                <h5 class="text-light fw-bold mb-3">Send us a Message</h5>
                <form action="<?= BASE_URL ?>actions/contact-action.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Your Name</label>
                        <input type="text" name="name" class="form-control bg-black border-secondary text-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <input type="email" name="email" class="form-control bg-black border-secondary text-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Message</label>
                        <textarea name="message" rows="4" class="form-control bg-black border-secondary text-light" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Send Inquiry</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
