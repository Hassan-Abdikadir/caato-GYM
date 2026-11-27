<?php
if (Auth::check()) redirect('dashboard');
$error = '';
if (is_post()) {
    [$ok, $msg] = Auth::login(req('username'), (string) ($_POST['password'] ?? ''));
    if ($ok) redirect('dashboard');
    $error = $msg;
}
include ROOT . '/app/views/login.php';
