<?php
declare(strict_types=1);
require_once __DIR__.'/lib/common.php';
header('Content-Type:text/plain; charset=utf-8');
$sql=file_get_contents(__DIR__.'/setup.sql');
try{ db()->exec($sql); echo "설치 완료\n보안을 위해 install.php는 삭제하거나 이름을 변경하세요."; }
catch(Throwable $e){ http_response_code(500); echo "설치 실패: ".$e->getMessage(); }
