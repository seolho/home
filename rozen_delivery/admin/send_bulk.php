<?php
declare(strict_types=1); require_once dirname(__DIR__).'/lib/ppurio.php'; require_admin(); verify_csrf();
$rows=db()->query("SELECT * FROM delivery_shipments WHERE send_status IN ('pending','failed') ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);$success=0;$fail=0;
foreach($rows as $r){
 try{$res=ppurio_send_delivery($r);}catch(Throwable $e){$res=['ok'=>false,'message'=>$e->getMessage(),'request'=>[],'response'=>[]];}
 $pdo=db();$log=$pdo->prepare('INSERT INTO delivery_send_logs(shipment_id,success,message_key,ref_key,request_json,response_json,error_message) VALUES(?,?,?,?,?,?,?)');$log->execute([$r['id'],$res['ok']?1:0,$res['message_key']??null,$res['ref_key']??null,json_encode($res['request']??[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),json_encode($res['response']??[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$res['ok']?null:$res['message']]);
 $up=$pdo->prepare("UPDATE delivery_shipments SET send_status=?,sent_at=?,send_count=send_count+1,last_error=?,message_key=?,ref_key=? WHERE id=?");$up->execute([$res['ok']?'success':'failed',$res['ok']?now_sql():null,$res['ok']?null:$res['message'],$res['message_key']??null,$res['ref_key']??null,$r['id']]);
 $res['ok']?$success++:$fail++; usleep(120000);
}
flash("전체 발송 완료: 성공 {$success}건 / 실패 {$fail}건",$fail?'warning':'success');redirect('index.php');
