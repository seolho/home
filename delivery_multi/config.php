<?php
declare(strict_types=1);

// 아주대의료원신협 공통 DB 설정 배열을 읽어 PDO 연결 생성
$dbCandidates = [
    dirname(__DIR__) . '/private_config/db_common.php',
    dirname(__DIR__, 2) . '/private_config/db_common.php',
    rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/private_config/db_common.php',
    dirname(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/')) . '/private_config/db_common.php',
];

$dbConfig = null;
foreach ($dbCandidates as $dbFile) {
    if ($dbFile !== '/private_config/db_common.php' && is_file($dbFile)) {
        $loaded = require $dbFile;
        if (is_array($loaded)) { $dbConfig = $loaded; break; }
    }
}
if (!is_array($dbConfig)) {
    throw new RuntimeException('private_config/db_common.php를 찾지 못했거나 DB 설정 배열을 읽지 못했습니다.');
}

if (!function_exists('db')) {
    function db(): PDO {
        global $dbConfig;
        static $pdo = null;
        if ($pdo instanceof PDO) return $pdo;
        $host = (string)($dbConfig['host'] ?? 'localhost');
        $name = (string)($dbConfig['name'] ?? '');
        $user = (string)($dbConfig['user'] ?? '');
        $pass = (string)($dbConfig['pass'] ?? '');
        $charset = (string)($dbConfig['charset'] ?? 'utf8mb4');
        if ($name === '' || $user === '') throw new RuntimeException('db_common.php의 name 또는 user 설정이 비어 있습니다.');
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    }
}

date_default_timezone_set('Asia/Seoul');

define('APP_NAME', '택배 배송안내 알림톡 관리');
define('APP_BASE_URL', '/delivery_multi'); // 설치 폴더명에 맞게 수정

define('ADMIN_PASSWORD_HASH', '$2y$12$aVE4LHVL1P5ZO4GgxeKdHexsFe2VD.JBr2uMlhn.nnmVGQDF/g7.u');

// 비즈뿌리오
define('PPURIO_ACCOUNT', 'aj9770');
define('PPURIO_AUTH_KEY', '08868d27d42a13b10954f7c9705063152e03d948b824bf336ff611be225957b9');
define('PPURIO_SENDER_PROFILE', '@타운카김설호');
// 현재 캡처의 다중택배 배송안내 템플릿 코드
define('PPURIO_TEMPLATE_CODE', 'ppur_2026091123092824417338051');
define('PPURIO_API_BASE', 'https://message.ppurio.com');
