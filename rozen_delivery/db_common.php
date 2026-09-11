<?php
/**
 * Cafe24 공통 DB 설정 샘플
 *
 * 권장 위치:
 *   /private_config/db_common.php
 *
 * 예시 서버 구조:
 *   /ajoucu/private_config/db_common.php
 *   /ajoucu/www/gift_club/
 *
 * 아래 값을 Cafe24 MySQL DB 정보에 맞게 수정하세요.
 * 이 파일에는 실제 비밀번호를 입력하므로 GitHub/public 저장소에 올리지 마세요.
 */

return [
    'host'    => 'localhost',       // 예: localhost 또는 Cafe24 DB 서버명
    'port'    => 3306,
    'name'    => 'seolhopro',    // DB명
    'user'    => 'seolhopro',    // DB 사용자명
    'pass'    => 'ajou2130--',// DB 비밀번호
    'charset' => 'utf8mb4',
];
