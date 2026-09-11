<?php
declare(strict_types=1); require_once dirname(__DIR__).'/lib/common.php'; require_once dirname(__DIR__).'/lib/xlsx.php'; require_admin(); verify_csrf();
$f=$_FILES['excel']??null; if(!$f || ($f['error']??1)!==UPLOAD_ERR_OK){ flash('업로드 파일을 확인해 주세요.','danger'); redirect('upload.php'); }
$ext=strtolower(pathinfo((string)$f['name'],PATHINFO_EXTENSION));
try{ $rows=$ext==='xlsx'?read_xlsx_rows($f['tmp_name']):($ext==='csv'?read_csv_rows($f['tmp_name']):throw new RuntimeException('XLSX 또는 CSV만 가능합니다.')); }
catch(Throwable $e){ flash($e->getMessage(),'danger'); redirect('upload.php'); }
$pdo=db(); $pdo->beginTransaction(); $ok=0;$skip=0;$replace=!empty($_POST['replace_same']);
try{
 foreach($rows as $i=>$r){
   $name=trim((string)($r[0]??''));$phone=normalize_phone((string)($r[1]??''));$product=trim((string)($r[2]??''));$tracking=normalize_tracking((string)($r[3]??''));
   if($i===0 && (mb_strpos($name,'성명')!==false || mb_strpos((string)($r[1]??''),'연락')!==false)){continue;}
   if($name===''||$phone===''||$product===''||$tracking===''){ $skip++; continue; }
   if($replace){
     $st=$pdo->prepare('SELECT id FROM delivery_shipments WHERE phone=? AND tracking_no=? LIMIT 1');$st->execute([$phone,$tracking]);$id=$st->fetchColumn();
     if($id){$up=$pdo->prepare("UPDATE delivery_shipments SET name=?,product_name=?,carrier_name=?,send_status='pending',sent_at=NULL,last_error=NULL WHERE id=?");$up->execute([$name,$product,FIXED_CARRIER_NAME,$id]);$ok++;continue;}
   }
   $ins=$pdo->prepare('INSERT INTO delivery_shipments(name,phone,product_name,carrier_name,tracking_no) VALUES(?,?,?,?,?)');$ins->execute([$name,$phone,$product,FIXED_CARRIER_NAME,$tracking]);$ok++;
 }
 $pdo->commit(); flash("업로드 완료: {$ok}건 / 제외 {$skip}건");
}catch(Throwable $e){$pdo->rollBack();flash('업로드 실패: '.$e->getMessage(),'danger');}
redirect('index.php');
