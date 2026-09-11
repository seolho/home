<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function normalize_phone(string $v): string { return preg_replace('/\D+/', '', $v) ?? ''; }
function normalize_tracking(string $v): string { return preg_replace('/[^0-9A-Za-z-]/', '', trim($v)) ?? ''; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void { $t=(string)($_POST['csrf']??''); if (!$t || !hash_equals((string)($_SESSION['csrf']??''), $t)) { http_response_code(419); exit('CSRF 오류'); } }
function flash(string $msg, string $type='success'): void { $_SESSION['flash']=['msg'=>$msg,'type'=>$type]; }
function pull_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function is_admin(): bool { return !empty($_SESSION['delivery_admin']); }
function require_admin(): void { if (!is_admin()) redirect(APP_BASE_URL.'/admin/login.php'); }
function now_sql(): string { return date('Y-m-d H:i:s'); }

function delivery_carriers(): array {
    return [
        '우체국택배','로젠택배','일양로지스','FedEX','한진택배','경동택배','합동택배',
        '롯데택배','농협택배','호남택배','천일택배','대신택배','건영택배','CU편의점택배',
    ];
}
function normalize_carrier(string $v): string {
    $v = trim($v);
    foreach (delivery_carriers() as $c) if ($v === $c) return $c;
    return '';
}
function carrier_options(string $selected=''): string {
    $html='';
    foreach (delivery_carriers() as $c) {
        $sel=$selected===$c?' selected':'';
        $html.='<option value="'.h($c).'"'.$sel.'>'.h($c).'</option>';
    }
    return $html;
}
