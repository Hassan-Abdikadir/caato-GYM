<?php
require __DIR__ . '/app/core/bootstrap.php';
Auth::boot();

$page = preg_replace('/[^a-z_]/', '', (string) ($_GET['page'] ?? 'dashboard'));
define('PAGE', $page);
if ($page !== 'login') {
    if (!Auth::check()) redirect('login');
    Membership::sync();
}
if (is_post()) csrf_check();

$file = ROOT . "/app/controllers/$page.php";
if (!is_file($file)) { http_response_code(404); render('errors/404'); exit; }
require $file;
