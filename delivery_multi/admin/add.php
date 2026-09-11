<?php
declare(strict_types=1);require_once __DIR__.'/_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$name=trim((string)($_POST['name']??''));$phone=normalize_phone((string)($_POST['phone']??''));$product=trim((string)($_POST['product_name']??''));$carrier=normalize_carrier((string)($_POST['carrier_name']??''));$tracking=normalize_tracking((string)($_POST['tracking_no']??''));
 if($name===''||$phone===''||$product===''||$carrier===''||$tracking===''){flash('모든 항목을 정확히 입력해 주세요.','danger');redirect('add.php');}
 $st=db()->prepare('INSERT INTO delivery_shipments(name,phone,product_name,carrier_name,tracking_no) VALUES(?,?,?,?,?)');$st->execute([$name,$phone,$product,$carrier,$tracking]);flash('배송정보가 등록되었습니다.');redirect('index.php');
}
?>
<div class="card border-0 shadow-sm" style="max-width:760px"><div class="card-body p-4"><h4 class="mb-1">배송정보 개별 등록</h4><p class="text-muted small">택배사는 승인된 배송조회 지원 택배사에서만 선택할 수 있습니다.</p><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<label class="form-label mt-2">성명</label><input class="form-control" name="name" required>
<label class="form-label mt-3">연락처</label><input class="form-control" name="phone" placeholder="01012345678" required>
<label class="form-label mt-3">상품명</label><input class="form-control" name="product_name" required>
<label class="form-label mt-3">택배사명</label><select class="form-select" name="carrier_name" required><option value="">선택해 주세요</option><?=carrier_options('로젠택배')?></select>
<label class="form-label mt-3">송장번호</label><input class="form-control" name="tracking_no" required>
<div class="mt-4"><button class="btn btn-primary"><i class="bi bi-plus-circle"></i> 등록</button> <a class="btn btn-outline-secondary" href="index.php">취소</a></div></form></div></div>
<?php require __DIR__.'/_footer.php'; ?>
