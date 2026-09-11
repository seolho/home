<?php
declare(strict_types=1);require_once __DIR__.'/lib/common.php';header('Content-Type:text/plain; charset=utf-8');
try{
 $pdo=db();$sql=file_get_contents(__DIR__.'/setup.sql');$pdo->exec($sql);
 // 기존 로젠 시스템 테이블을 그대로 쓰는 경우 carrier_name 컬럼이 없을 때만 추가
 $cols=$pdo->query("SHOW COLUMNS FROM delivery_shipments LIKE 'carrier_name'")->fetchAll();
 if(!$cols)$pdo->exec("ALTER TABLE delivery_shipments ADD carrier_name VARCHAR(50) NOT NULL DEFAULT '로젠택배' AFTER product_name");
 echo "설치/확인 완료\n기존 delivery_shipments / delivery_send_logs 테이블을 함께 사용합니다.\n보안을 위해 install.php는 삭제하거나 이름을 변경하세요.";
}catch(Throwable $e){http_response_code(500);echo '설치 실패: '.$e->getMessage();}
