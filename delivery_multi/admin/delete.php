<?php
declare(strict_types=1); require_once dirname(__DIR__).'/lib/common.php'; require_admin();verify_csrf();$id=(int)($_POST['id']??0);$st=db()->prepare('DELETE FROM delivery_shipments WHERE id=?');$st->execute([$id]);flash('삭제했습니다.');redirect('index.php');
