<?php
/**
 * Admin Logout
 */
require_once '../config/database.php';
require_once '../config/auth.php';

logout();
header('Location: login.php');
exit;


