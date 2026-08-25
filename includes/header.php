<?php
/**
 * Discora - Shared Frontend Header
 * Expects optional variables: $page_title, $page_css (array of custom css files)
 */
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';
require_once dirname(__DIR__) . '/core/auth.php';

$title = isset($page_title) ? $page_title . " | " . APP_NAME : APP_NAME . " - " . APP_TAGLINE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Google Fonts: Rajdhani for bold gaming headlines, Inter for clean UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Rajdhani:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Discora Core Stylesheets -->
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/responsive.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/auth-modal.css?v=<?= time() ?>">
    
    <!-- Page Specific CSS -->
    <?php if (isset($page_css) && is_array($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link rel="stylesheet" href="<?= ASSETS_PATH ?>css/pages/<?= htmlspecialchars($css) ?>?v=<?= time() ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($body_class ?? '') ?>">
<?php require_once INCLUDES_PATH . 'navbar.php'; ?>
