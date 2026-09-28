<?php
/**
 * Discora - Flash Alerts Renderer
 */
require_once dirname(__DIR__) . '/config/session.php';

$flash = get_flash_message();
if ($flash):
    $alert_class = htmlspecialchars($flash['type'] ?? 'info');
    $alert_msg = htmlspecialchars($flash['message'] ?? '');
?>
<div class="alert alert-<?= $alert_class ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
    <div>
        <?= $alert_msg ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
