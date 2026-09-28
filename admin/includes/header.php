<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/db.php';

$header_db = get_db_connection();

// Fetch Pending Notifications
$notif_pending_reviews = $header_db->query("SELECT COUNT(*) FROM reviews WHERE review_status = 'Pending'")->fetchColumn();
$notif_pending_orders = $header_db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$total_notifications = $notif_pending_reviews + $notif_pending_orders;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($admin_title) ? $admin_title . ' - ' : '' ?>Discora Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- GSAP for Admin Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <style>
        :root {
            --admin-bg: #f8fafc;
            --admin-sidebar-bg: #121212;
            --admin-border: #e2e8f0;
            --admin-sidebar-width: 260px;
        }

        body {
            background-color: var(--admin-bg);
            font-family: system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--admin-sidebar-width);
            background-color: var(--admin-sidebar-bg) !important;
            flex-shrink: 0;
            transition: transform 0.3s ease, width 0.3s ease;
            z-index: 1050;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-sidebar .sidebar-nav {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .admin-sidebar::-webkit-scrollbar,
        .admin-sidebar .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        .admin-sidebar::-webkit-scrollbar-thumb,
        .admin-sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }

        .admin-main {
            flex-grow: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .admin-topbar {
            height: 60px;
            background: white;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }

        .admin-content {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .font-heading {
            font-family: "Outfit", sans-serif;
        }

        .card {
            border: 1px solid var(--admin-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        /* Mobile Sidebar */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
        }

        /* Minimized Sidebar (Desktop) */
        @media (min-width: 992px) {
            .admin-sidebar.minimized {
                width: 80px;
            }

            .admin-sidebar.minimized .nav-link span,
            .admin-sidebar.minimized .sidebar-header .logo-img,
            .admin-sidebar.minimized .sidebar-section-title,
            .admin-sidebar.minimized .user-info {
                display: none !important;
            }
            
            .admin-sidebar.minimized .sidebar-header .logo-icon {
                display: block !important;
            }
            
            .admin-sidebar.minimized .nav-link {
                justify-content: center;
                padding: 0.8rem 0;
            }
            
            .admin-sidebar.minimized .nav-link i {
                margin-right: 0 !important;
                font-size: 1.4rem;
            }

            .admin-sidebar.minimized .admin-profile-sidebar {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-light border d-none d-lg-inline-block me-3" id="desktopMenuToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <button class="btn btn-sm btn-light border d-lg-none me-2" id="mobileMenuToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h5 class="mb-0 fw-bold font-heading text-dark text-truncate d-none d-sm-block" style="max-width: 150px;"><?= isset($admin_title) ? htmlspecialchars($admin_title) : 'Dashboard' ?></h5>
            </div>
            <div class="d-flex align-items-center gap-1 gap-sm-3">
                <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center px-2 px-sm-3">
                    <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-sm-inline ms-1">View Store</span>
                </a>
                
                <!-- Notification Bell -->
                <div class="dropdown">
                    <button class="btn btn-light position-relative rounded-circle p-2 border-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $total_notifications > 0 ? '' : 'd-none' ?>" id="notificationBadge">
                            <?= $total_notifications ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 p-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notificationList">
                        <?php if ($total_notifications == 0): ?>
                            <li class="p-3 text-center text-muted small" id="noNotifications">No new notifications</li>
                        <?php else: ?>
                            <?php if ($notif_pending_reviews > 0): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="<?= ADMIN_URL ?>reviews.php?status=Pending">
                                    <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 me-3">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small text-dark"><?= $notif_pending_reviews ?> Pending Review(s)</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Requires your approval</div>
                                    </div>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if ($notif_pending_orders > 0): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="<?= ADMIN_URL ?>orders.php?status=Pending">
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 me-3">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small text-dark"><?= $notif_pending_orders ?> Pending Order(s)</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Awaiting processing</div>
                                    </div>
                                </a>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Admin Profile -->
                <div class="dropdown">
                    <button class="btn btn-light border-0 d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($_SESSION['user_avatar'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Admin Avatar" class="rounded-circle border border-secondary" style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <?php if (is_superadmin()): ?>
                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="admin-content">
