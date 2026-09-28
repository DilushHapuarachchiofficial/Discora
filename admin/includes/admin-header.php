<?php
/**
 * Discora - Admin Dashboard Header
 */
require_once __DIR__ . '/admin-auth-check.php';
$admin_title = isset($page_title) ? $page_title . " - Admin | " . APP_NAME : "Admin Dashboard | " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_title) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/admin.css">
</head>
<body class="bg-dark text-light">
<div class="d-flex" id="wrapper">
    <?php require_once __DIR__ . '/admin-sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary border-opacity-25 px-4 py-3">
            <div class="d-flex align-items-center justify-content-between w-100">
                <h5 class="mb-0 text-light"><?= $page_title ?? 'Dashboard' ?></h5>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-up-right me-1"></i> Visit Store</a>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </nav>
        <div class="container-fluid p-4">
            <?php require_once INCLUDES_PATH . 'alerts.php'; ?>
