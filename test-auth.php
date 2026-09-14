<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST['action'] = 'register';
$_POST['first_name'] = 'Test';
$_POST['last_name'] = 'User';
$_POST['email'] = 'testuser123@example.com';
$_POST['phone'] = '1234567890';
$_POST['password'] = 'password123';
$_POST['confirm_password'] = 'password123';
$_POST['terms'] = 'on';

require 'actions/auth-action.php';
