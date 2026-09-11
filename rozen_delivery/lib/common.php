<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function normalize_phone(string $v): string { return preg_replace('/\D+/', '', $v) ?? ''; }
function normalize_tracking(string $v): string { return preg_replace('/[^0-9A-Za-z-]/', '', trim($v)) ?? ''; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void {
    $t=(string)($_POST['csrf']??'');
    if (!$t || !hash_equals((string)($_SESSION['csrf']??''), $t)) { http_response_code(419); exit('CSRF 오류'); }
}
function flash(string $msg, string $type='success'): void { $_SESSION['flash']=['msg'=>$msg,'type'=>$type]; }
function pull_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function is_admin(): bool { return !empty($_SESSION['delivery_admin']); }
function require_admin(): void { if (!is_admin()) redirect(APP_BASE_URL.'/admin/login.php'); }
function now_sql(): string { return date('Y-m-d H:i:s'); }
function make_ref_key(int $id): string { return 'dlv'.date('ymdHis').substr(hash('sha256',$id.'|'.microtime(true).'|'.random_bytes(8)),0,18); }
