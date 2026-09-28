<?php
require_once dirname(__DIR__) . '/config/constants.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
header("Location: " . BASE_URL . "index.php");
exit();
