<?php
header('Content-Type:text/html; charset=utf-8');
$hash=''; if($_SERVER['REQUEST_METHOD']==='POST'){ $pw=(string)($_POST['pw']??''); if($pw!=='') $hash=password_hash($pw,PASSWORD_DEFAULT); }
?><!doctype html><html lang="ko"><meta charset="utf-8"><title>관리자 비밀번호 해시</title><body style="font-family:sans-serif;max-width:700px;margin:50px auto"><h2>관리자 비밀번호 해시 생성</h2><form method="post"><input type="password" name="pw" required style="padding:10px;width:300px"><button style="padding:10px 18px">생성</button></form><?php if($hash):?><p>config.php의 ADMIN_PASSWORD_HASH 값으로 교체하세요.</p><textarea style="width:100%;height:100px"><?=htmlspecialchars($hash)?></textarea><?php endif?></body></html>
