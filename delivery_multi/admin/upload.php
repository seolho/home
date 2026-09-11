<?php
declare(strict_types=1); require_once __DIR__.'/_header.php'; ?>
<div class="row g-4"><div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body p-4">
<h4>엑셀 일괄 업로드</h4><p class="text-muted">첫 번째 시트를 읽습니다. XLSX 또는 CSV를 지원합니다.</p>
<div class="alert alert-info"><b>열 순서</b><br>성명 / 연락처 / 상품명 / 택배사명 / 송장번호<br><span class="small">택배사명은 아래 지원목록과 <b>정확히 동일</b>해야 합니다.</span></div>
<div class="small mb-3"><b>이용가능 택배사:</b> <?=h(implode(', ',delivery_carriers()))?></div>
<form method="post" action="upload_process.php" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="file" class="form-control mb-3" name="excel" accept=".xlsx,.csv" required><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="replace_same" id="replace" value="1" checked><label class="form-check-label" for="replace">같은 연락처+송장번호가 있으면 기존 행 업데이트</label></div><button class="btn btn-primary btn-lg"><i class="bi bi-upload"></i> 업로드</button> <a class="btn btn-outline-secondary btn-lg" href="sample.csv">샘플 CSV</a></form>
</div></div></div><div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h5>예시</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>성명</th><th>연락처</th><th>상품명</th><th>택배사명</th><th>송장번호</th></tr></thead><tbody><tr><td>홍길동</td><td>01012345678</td><td>추석 선물</td><td>로젠택배</td><td>12345678901</td></tr></tbody></table></div><div class="small text-muted">송장번호는 엑셀에서 <b>텍스트 형식</b>으로 저장하는 것을 권장합니다.</div></div></div></div></div>
<?php require __DIR__.'/_footer.php'; ?>
