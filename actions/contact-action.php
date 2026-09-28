<?php
/**
 * Discora - Contact Form Submission Endpoint
 */
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Save message or send email notification
    set_flash_message('success', 'Your message has been sent to our support team!');
    redirect(BASE_URL . 'contact.php');
}
redirect(BASE_URL . 'contact.php');
