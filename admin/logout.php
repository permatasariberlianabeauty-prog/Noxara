<?php
require_once __DIR__ . '/../config/bootstrap.php';
unset($_SESSION['admin_id'], $_SESSION['admin_role']);
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
