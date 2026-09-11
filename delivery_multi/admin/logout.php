<?php require_once dirname(__DIR__).'/lib/common.php'; session_destroy(); header('Location: '.APP_BASE_URL.'/admin/login.php'); exit;
