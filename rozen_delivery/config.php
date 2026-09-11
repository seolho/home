<?php
declare(strict_types=1);

// 아주대의료원신협 공통 DB 설정 파일은 PDO 객체가 아니라 배열을 return 합니다.
// 예: ['host'=>'localhost','name'=>'...','user'=>'...','pass'=>'...','charset'=>'utf8mb4']
$dbCandidates = [
    dirname(__DIR__) . '/private_config/db_common.php',
    dirname(__DIR__, 2) . '/private_config/db_common.php',
    rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/private_config/db_common.php',
    dirname(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/')) . '/private_config/db_common.php',
];

$dbConfig = null;
$dbConfigPath = null;
foreach ($dbCandidates as $dbFile) {
    if ($dbFile !== '/private_config/db_common.php' && is_file($dbFile)) {
        $loaded = require $dbFile;
        if (is_array($loaded)) {
            $dbConfig = $loaded;
            $dbConfigPath = $dbFile;
            break;
        }
    }
}
if (!is_array($dbConfig)) {
    throw new RuntimeException('private_config/db_common.php를 찾지 못했거나 DB 설정 배열을 읽지 못했습니다. config.php의 $dbCandidates를 설치경로에 맞게 확인하세요.');
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

        if ($name === '' || $user === '') {
            throw new RuntimeException('db_common.php의 name 또는 user 설정이 비어 있습니다.');
        }

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    }
}

date_default_timezone_set('Asia/Seoul');

define('APP_NAME', '로젠택배 배송안내 관리');
define('APP_BASE_URL', '/rozen_delivery'); // 설치 경로에 맞게 수정

// 관리자 비밀번호 해시(make_hash.php로 생성 후 교체)
define('ADMIN_PASSWORD_HASH', '$2y$12$ELlvmVO.OioHtBW4WYT91Oqz0cwP50IpJ46Xl2ZxVb3lrCCXtOs66');

// 비즈뿌리오
// 운영 계정값은 public_html 밖 private_config에 두는 것을 권장합니다.
define('PPURIO_ACCOUNT', 'aj9770');
define('PPURIO_AUTH_KEY', '08868d27d42a13b10954f7c9705063152e03d948b824bf336ff611be225957b9');
define('PPURIO_SENDER_PROFILE', '@타운카김설호');
define('PPURIO_TEMPLATE_CODE', 'ppur_2026091120431547407176682');
define('PPURIO_API_BASE', 'https://message.ppurio.com');

define('FIXED_CARRIER_NAME', '로젠택배');

