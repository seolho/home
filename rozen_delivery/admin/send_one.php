<?php
declare(strict_types=1); require_once dirname(__DIR__).'/lib/ppurio.php'; require_admin(); verify_csrf(); $id=(int)($_POST['id']??0);
$st=db()->prepare('SELECT * FROM delivery_shipments WHERE id=?');$st->execute([$id]);$r=$st->fetch(PDO::FETCH_ASSOC); if(!$r){flash('대상을 찾을 수 없습니다.','danger');redirect('index.php');}
$res=ppurio_send_delivery($r); $pdo=db();
$log=$pdo->prepare('INSERT INTO delivery_send_logs(shipment_id,success,message_key,ref_key,request_json,response_json,error_message) VALUES(?,?,?,?,?,?,?)');$log->execute([$id,$res['ok']?1:0,$res['message_key']??null,$res['ref_key']??null,json_encode($res['request']??[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),json_encode($res['response']??[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$res['ok']?null:$res['message']]);
$up=$pdo->prepare("UPDATE delivery_shipments SET send_status=?,sent_at=?,send_count=send_count+1,last_error=?,message_key=?,ref_key=? WHERE id=?");$up->execute([$res['ok']?'success':'failed',$res['ok']?now_sql():null,$res['ok']?null:$res['message'],$res['message_key']??null,$res['ref_key']??null,$id]);
flash($res['ok']?'알림톡 발송요청이 정상 접수되었습니다.':'발송 실패: '.$res['message'],$res['ok']?'success':'danger');redirect('index.php');
