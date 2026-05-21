<?php
require_once __DIR__ . '/../config/bootstrap.php';
do_logout();
redirect(BASE_URL . '/auth/login.php');
