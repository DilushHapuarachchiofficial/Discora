<?php
/**
 * Discora - Search Handler & Redirect
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/functions.php';

$query = sanitize_input($_GET['q'] ?? $_GET['search'] ?? '');
if (!empty($query)) {
    header("Location: " . BASE_URL . "products.php?search=" . urlencode($query));
} else {
    header("Location: " . BASE_URL . "products.php");
}
exit;
